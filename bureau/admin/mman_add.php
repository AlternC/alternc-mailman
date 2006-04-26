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

if (!$quota->cancreate("mailman")) {
	$error=$err->errstr();
}
include("head.php");
?>
</head>
<body>
<h3><?php __("Mailing lists"); ?></h3>
<?php
	if ($error) {
		echo "<p class=\"error\">$error</p></body></html>";
	}
?>
<form method="post" action="mman_doadd.php">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<table>
<tr><th><label for="login"><?php __("Email address"); ?></label></th><td>
	<input type="text" class="int" id="login" name="login" value="<?php echo $login; ?>" size="20" maxlength="64" /><b>&nbsp;@&nbsp;</b><select class="inl" name="domain"><?php $mailman->select_prefix_list($domain); ?></select>
</td></tr>
<tr><th><label for="owner"><?php __("Owner"); ?> *</label></th><td>
	Email : <input type="text" class="int" id="owner" name="owner" value="<?php echo $owner; ?>" size="20" maxlength="64" />
	</td></tr>
<tr><th><label for="pass"><?php __("Initial list password"); ?> </label></th><td>
	<input type="text" class="int" id="pass" name="pass" value="<?php echo $pass; ?>" size="20" maxlength="64" />
	</td></tr>
<tr><td colspan="2">
  <input type="submit" class="inb" name="submit" value="<?php __("Create the list."); ?>"/>
</td></tr>
</table>
</form>

</body>
</html>
