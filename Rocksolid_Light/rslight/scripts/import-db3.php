<?php
/* This script allows importing a group .db3 file from a backup
 * or another rslight site.
 * 
 * Place the article database file group.name-articles.db3 in 
 * your spool directory, and change user/group to your web user.
 * Run this script as your web user from your $webdir/spoolnews dir:
 * php $config_dir/scripts/import.php group.name
 *
 * This will create the overview files necessary to import the group
 * into your site.
 * Next: Add the group to the groups.txt file of the section you wish
 * it to appear:
 * $config_dir/<section>/groups.txt
*/

include "config.inc.php";
include ("$file_newsportal");

$logfile=$logdir.'/import.log';

# END MAIN CONFIGURATION

$workpath=$spooldir."/";
$path=$workpath."articles/";

$lockfile = sys_get_temp_dir() . '/'.$config_name.'-import.lock';
$pid = file_get_contents($lockfile);
if (posix_getsid($pid) === false || !is_file($lockfile)) {
   print "Starting Import...\n";
   file_put_contents($lockfile, getmypid()); // create lockfile
} else {
   print "Import currently running\n";
   exit;
}

$group = $argv[1];
import_articles($group);
echo "\nSpoolnews Done\r\n";

function import_articles($group) {
  global $spooldir, $CONFIG, $workpath, $path, $config_name, $logfile;

  # Prepare databases
// Overview db
  $database = $spooldir.'/articles-overview.db3';
  $table = 'overview';
  $dbh = rslight_db_open($database, $table);
  $sql = 'INSERT INTO '.$table.'(newsgroup, number, msgid, date, name, subject) VALUES(?,?,?,?,?,?)';
  $stmt = $dbh->prepare($sql);
// Incoming db
  $article_dbh = article_db_open($spooldir.'/'.$group.'-articles.db3');
  $article_stmt = $article_dbh->query('SELECT * FROM articles');
  while ($row = $article_stmt->fetch()) {
      $local = $row['number'];
      $this_article = preg_split("/\r\n|\n|\r/", $row['article']);
      $lines=0;
      $bytes=0;
      $ref=0;
      $banned=0;
      $is_header=1;
      foreach($this_article as $response) 
      {
	$bytes = $bytes + mb_strlen($response, '8bit');	
	if(trim($response) == "" || $lines > 0) {
	  $is_header=0;
	  $lines++;
	}
       if($is_header == 1) {
	$response = str_replace("\t", " ", $response);
	// Find article date
	if(stripos($response, "Date: ") === 0) {
	  $finddate=explode(': ', $response, 2);
	  $article_date = strtotime($finddate[1]);
	  $ref=0;
	}
	// Get overview data
        $mid[1] = $row['msgid'];
	$from[1] = $row['name'];
	$subject[1] = $row['subject'];
	
	if(stripos($response, "Xref: ") === 0) {
	  if(isset($CONFIG['enable_nntp']) && $CONFIG['enable_nntp'] == true) {
	    $response="Xref: ".$CONFIG['pathhost']." ".$group.":".$local;
	  } 
          $xref=$response;
	  $ref=0;
        }
	if(stripos($response, "References: ") === 0) {
	  $this_references=explode('References: ', $response);
	  $references = $this_references[1];
	  $ref=1;
	}
	if((stripos($response, ':') === false) && (strpos($response, '>'))) {
	  if($ref == 1) {
  	    $references=$references.$response;
	  }
	}
       $response=str_replace("\n","",str_replace("\r","",$response));
      }
   }
      $lines=$lines-1;
      $bytes = $bytes + ($lines * 2);
// Overview
      $overviewHandle = fopen($workpath.$group."-overview", 'a');
      fputs($overviewHandle, $local."\t".$subject[1]."\t".$from[1]."\t".$finddate[1]."\t".$mid[1]."\t".$references."\t".$bytes."\t".$lines."\t".$xref."\n");
      fclose($overviewHandle);
      $references="";
// add to database
	$stmt->execute([$group, $local, $mid[1], $article_date, $from[1], $subject[1]]);
      echo "\nImported: ".$group." ".$local;
      file_put_contents($logfile, "\n".format_log_date()." ".$config_name." Imported: ".$group.":".$local, FILE_APPEND);
      $i++;
  }
  $article_dbh = null;
  $dbh = null;
}
?>
