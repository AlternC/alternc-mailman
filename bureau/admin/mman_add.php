<?php
/*
 $Id$
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
 Purpose of file: ask for the required values to create a mailing-list
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

if (!$quota->cancreate("mailman")) {
	$error=$err->errstr();
}

?>
<h3><?php __("Mailing lists"); ?></h3>
<hr id="topbar"/>
<br />
<?php
	if ($error) {
		echo "<p class=\"error\">$error</p>";
	}
?>
<form method="post" action="mman_doadd.php" name="main" id="main" >
<input type="hidden" name="id" value="<?php echo $id ?>" />
<table class="tedit">
<tr><th><label for="login"><?php __("List's email address"); ?></label></th><td>
	<input type="text" class="int" id="login" name="login" value="<?php ehe($login); ?>" size="20" maxlength="64" /><b>&nbsp;@&nbsp;</b><select class="inl" name="domain"><?php $mailman->select_prefix_list($domain); ?></select>
</td></tr>
	<tr><th><label for="owner"><?php __("Email of the list's administrator"); ?> </label></th><td>
	<input type="text" class="int" id="owner" name="owner" value="<?php ehe($owner); ?>" size="20" maxlength="64" />
	</td></tr>
<tr><th><label for="pass"><?php __("List password"); ?> </label></th><td>
	<input type="password" class="int" id="pass" name="pass" value="<?php ehe($pass); ?>" size="20" maxlength="64" />
	</td></tr>
<tr><th><label for="pass"><?php __("List password (confirm)"); ?> </label></th><td>
	<input type="password" class="int" id="pass2" name="pass2" value="<?php ehe($pass2); ?>" size="20" maxlength="64" />
	</td></tr>
<tr class="trbtn"><td colspan="2">
  <input type="submit" class="inb" name="submit" value="<?php __("Create the list."); ?>"/>
  <input type="button" class="inb" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='mman_list.php'"/>
</td></tr>
</table>
</form>

<script type="text/javascript">
  document.forms['main'].login.focus();
  document.forms['main'].setAttribute('autocomplete', 'off');
</script>

<?php include_once("foot.php"); ?>
