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
 Original Author of file: Benjamin Sonntag, Franck Missoum
 Purpose of file: Show the Mailing-Lists owned by the current user
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");
?>
	<h3><?php __("Mailing lists"); ?></h3>

<?php
// If there is no installed domain, let's failed definitely !
if (count($dom->enum_domains())==0) {
  $error=_("No domain is installed on your account, you cannot create any mailing list!");
  ?>
<hr id="topbar"/>
<br />
	 <?php echo "<p class=\"error\">$error</p>"; ?>
<?php include_once("foot.php");
  exit();
	 }

if(!$r=$mailman->enum_ml()) {
  $error.=$err->errstr();
	?>
<hr id="topbar"/>
<br />
	<?php echo "<p class=\"error\">$error</p>"; ?>
<?php
if ($quota->cancreate("mailman")) {
?>
<p>
<span class="inb"><a href="mman_add.php"><?php __("Create a list"); ?></a></span>
</p>
	<?php
}
?>

	<?php
} else {
	?>
<hr id="topbar"/>
<br />
 <?php if ($error) echo "<p class=\"error\">$error</p>"; ?>

<?php
if ($quota->cancreate("mailman")) {
?>
<p>
<span class="inb"><a href="mman_add.php"><?php __("Create a list"); ?></a></span>
</p>
	<?php
}
?>


	<form method="post" action="mman_del.php">
	<table class="tlist">
	<tr><th><?php __("Delete"); ?></th><th><?php __("List name"); ?></th><th colspan="4">&nbsp;</th></tr>
	<?php
	reset($r);
	$col=1;
	while (list($key,$val)=each($r)) {
		$col=3-$col;
		?>
		<tr class="lst<?php echo $col; ?>">
		   <td align="center" rowspan="2"><?php if ($val["list"]!="mailman") { ?><input type="checkbox" class="inc" name="d[]" value="<?php echo $val["id"]; ?>" id="d_<?php echo $val["id"]; ?>" /><?php } ?></td>
		   <td rowspan="2"><label for="d_<?php echo $val["id"]; ?>"><?php echo $val["list"]."@".$val["domain"] ?></label></td>
			<td><div class="ina"><a href="http://<?php echo $L_FQDN; ?>/cgi-bin/mailman/listinfo/<?php echo $val["name"] ?>"><?php __("Public page"); ?></a></div></td>
			<td><div class="ina"><a href="http://<?php echo $L_FQDN; ?>/cgi-bin/mailman/admin/<?php echo $val["name"] ?>"><?php __("List admin"); ?></a></div></td>
			<td><div class="ina"><a href="http://<?php echo $L_FQDN; ?>/cgi-bin/mailman/admindb/<?php echo $val["name"] ?>"><?php __("Pending messages"); ?></a></div></td>
                        <td></td>
</tr><tr class="lst<?php echo $col; ?>">
			<td><div class="ina"><a href="mman_passwd.php?id=<?php echo $val["id"] ?>"><?php __("Change password"); ?></a></div></td>
			<td><div class="ina"><a href="mman_url.php?id=<?php echo $val["id"] ?>"><?php __("Change url"); ?></a></div></td>
			<td><div class="ina"><a href="mman_members.php?id=<?php echo $val["id"] ?>"><?php __("List Members"); ?></a></div></td>
			<td><div class="ina"><a href="mman_syncmembers.php?id=<?php echo $val["id"] ?>"><?php __("Sync Members"); ?></a></div></td>
		</tr>
		<?php
		}
	?>
	</table>
<br />
<input type="submit" class="inb" name="submit" value="<?php __("Delete the checked lists"); ?>" />
</form>

	<?php
	    }

?>


<?php include_once("foot.php"); ?>
