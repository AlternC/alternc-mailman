<?php
/*
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2000-2012 by the AlternC Development Team.
 https://alternc.org/
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
 Purpose of file: ask for the required value to change a list's password
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

$fields = array (
		 "id"     => array ("request", "integer", ""),
		 );
getFields($fields);

$error="";
if (!($me=$mailman->get_lst($id))) {
  $error=$err->errstr();
  ?>
	  <h3><?php __("Mailing lists"); ?></h3>
<?php 
 echo "<p class=\"error\">$error</p>";
 include_once("foot.php");
 exit();
}

?>
<h3><?php __("Mailing lists"); ?></h3>
<?php
	if ($error) {
		echo "<p class=\"error\">$error</p>";
	}

?>
<form method="post" action="mman_dopasswd.php" name="main" id="main">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<?php echo "<h3>".sprintf(_("Changing password of list %s"),$me)."</h3>"; ?>
<table class="tedit">
<tr><th><label for="pass"><?php __("New list password"); ?> </label></th><td>
	<input type="password" class="int" id="pass" name="pass" value="" size="20" maxlength="64" />
	</td></tr>
<tr><th><label for="pass2"><?php __("Password confirmation"); ?> </label></th><td>
	<input type="password" class="int" id="pass2" name="pass2" value="" size="20" maxlength="64" />
	</td></tr>
<tr><td colspan="2">
  <input type="submit" class="inb" name="submit" value="<?php __("Change the password."); ?>"/>
</td></tr>
</table>
  </form>

<script type="text/javascript">
  $(document).ready(function() {
      $('#pass').focus();
      $('#pass').attr('autocomplete','off');
      $('#pass2').attr('autocomplete','off');
    });
</script>

<?php include_once("foot.php"); ?>