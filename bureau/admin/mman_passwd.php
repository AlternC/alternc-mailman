<?php
/*
 $Id: mman_passwd.php 2516 2009-06-14 12:42:14Z benjamin $
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2002 by the AlternC Development Team.
 http://alternc.org/
 ----------------------------------------------------------------------
 Based on:
 Valentin Lacambre's web hosting softwares: http://altern.org/
 ----------------------------------------------------------------------
 LICENSE

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License (GPL)
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 To read the license please visit http://www.gnu.org/copyleft/gpl.html
 ----------------------------------------------------------------------
 Original Author of file: Benjamin Sonntag
 Purpose of file: ask for the required value to change a list's password
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

$id=intval($_REQUEST["id"]);

if (!($me=$mailman->get_lst($id))) {
	$error=$err->errstr();
}

?>
<h3><?php __("Mailing lists"); ?></h3>
<?php
	if ($error) {
		echo "<p class=\"error\">$error</p>";
		include_once("foot.php");
		exit();
	}
?>
<form method="post" action="mman_dopasswd.php">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<?php echo "<h3>".printf(_("Changing password of list %s"),$me)."</h3>"; ?>
<table>
<tr><th><label for="pass"><?php __("New list password"); ?> </label></th><td>
	<input type="password" class="int" id="pass" name="pass" value="<?php echo $pass; ?>" size="20" maxlength="64" />
	</td></tr>
<tr><th><label for="pass2"><?php __("Password confirmation"); ?> </label></th><td>
	<input type="password" class="int" id="pass2" name="pass2" value="" size="20" maxlength="64" />
	</td></tr>
<tr><td colspan="2">
  <input type="submit" class="inb" name="submit" value="<?php __("Change the password."); ?>"/>
</td></tr>
</table>
</form>
<?php include_once("foot.php"); ?>