<html>
<?php include "head.inc";?>
<table width=100% border="0" align="center" cellpadding="0" cellspacing="1">
<tr>
<form name="form1" method="post" action="change.php">
<td>
<table width="100%" border="0" cellpadding="3" cellspacing="1">
<tr>
<td colspan="3"><strong>Change Password </strong></td>
</tr>
<tr>
<td width="78">Username</td>
<td width="6">:</td>
<td width="294"><input name="username" type="text" id="username"></td>
</tr>
<tr>
<td>Current Password</td>
<td>:</td>
<td><input name="current" type="password" id="password"></td>
</tr>
<tr>
<td>New Password</td>
<td>:</td>
<td><input name="password" type="password" id="password"></td>
</tr>
<tr>
<td>Re-enter Password</td>
<td>:</td>
<td><input name="password2" type="password" id="password2"></td>
</tr>
<tr>
<td><input name="command" type="hidden" id="command" value="Change" readonly="readonly"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><input type="submit" name="Submit" value="Change Password"></td>
</tr>
</table>
</td>
</form>
</tr>
</table>
</body>
</html>
