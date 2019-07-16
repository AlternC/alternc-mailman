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
 Purpose of file: Show the Mailing-Lists owned by the current user
 ----------------------------------------------------------------------
*/

require_once("../class/config.php");
include_once("head.php");

$mman_status=array(
		   "PASSWORD" => _("Password change pending"),
		   "SETURL" => _("Url change pending"),
		   "CREATE" => _("List creation pending"),
		   );


// If there is no installed domain, let's failed definitely !
if (count($mailman->prefix_list())==0) {
    $msg->raise("ALERT","mailman",_("No domain is installed on your account, you cannot create any mailing list!"));
?>
<h3><?php __("Mailing lists"); ?></h3>
<hr id="topbar"/>
<br />
<?php 
    echo $msg->msg_html_all();
 include_once("foot.php");
  exit();
}

if(!$r=$mailman->enum_ml()) {
  if ($quota->cancreate("mailman")) {
    require_once("mman_add.php"); 
    exit();
  } else {
    require_once("main.php");
    exit();
  }
} else {
	?>
<h3><?php __("Mailing lists"); ?></h3>
<hr id="topbar"/>
<br />
 <?php 
    echo $msg->msg_html_all();

if ($quota->cancreate("mailman")) {
?>
<p>
<span class="ina"><a href="mman_add.php"><?php __("Create a list"); ?></a></span>
</p>
	<?php
}
?>


	<form method="post" action="mman_edit.php">
<?php csrf_get(); ?>
	<table class="tlist">
	<tr><th><?php __("Select"); ?></th><th><?php __("List name"); ?></th><th><?php __("List Status"); ?><th colspan="3">&nbsp;</th></tr>
	<?php
//	$list_base_url = variable_get('mailman_url',      $L_FQDN,'URL used to build the list URL, must match DEFAULT_URL_HOST in mm_cfg.py');
// now using "url" in the mailman table
	reset($r);
	$col=1;
	while (list($key,$val)=each($r)) {
		$col=3-$col;
		?>
		<tr class="lst<?php echo $col; ?>">
		   <?php if ($val["mailman_action"]=="DELETE" || $val["mailman_action"]=="DELETING")  { ?>
		   <td></td>
	    <td><?php echo $val["list"]."@".$val["domain"] ?></td>
	    <td colspan="4"><?php __("List is pending deletion, you can't do anything on it"); ?></td>
		   <?php } else { ?>
		   <td align="center" rowspan="2"><?php if ($val["list"]!="mailman") { ?><input type="checkbox" class="inc" name="d[]" value="<?php echo $val["id"]; ?>" id="d_<?php echo $val["id"]; ?>" /><?php } ?></td>
		   <td rowspan="2"><label for="d_<?php echo $val["id"]; ?>"><?php echo $val["list"]."@".$val["domain"] ?></label></td>
	   <td rowspan="2"><?php if (isset($val["mailman_action"]) && $val["mailman_action"]!="OK") { ?>
		     <?php echo _($mman_status[$val["mailman_action"]]); ?>
<?php } elseif (!empty($val["mailman_result"])) { ?>
	  <?php echo $val["mailman_result"]; /* strings present for gettext in m_mailman */ ?>
<?php } else { echo "OK";}
      
      ?></td>
			<td><div class="ina"><a target=_blank href="https://<?php echo $val["url"]; ?>/cgi-bin/mailman/admin/<?php echo $val["name"] ?>"><?php __("List admin"); ?></a></div></td>
			<td><div class="ina"><a target=_blank href="https://<?php echo $val["url"]; ?>/cgi-bin/mailman/admindb/<?php echo $val["name"] ?>"><?php __("Pending messages"); ?></a></div></td>
<!--		    <td>&nbsp;</td> -->
</tr><tr class="lst<?php echo $col; ?>">
			<td><div class="ina"><a href="mman_passwd.php?id=<?php echo $val["id"] ?>"><?php __("Change password"); ?></a></div></td>
			<td><div class="ina"><a href="mman_url.php?id=<?php echo $val["id"] ?>"><?php __("Change url"); ?></a></div></td>
	      <?php } ?>
		</tr>
		<?php
		}
	?>
	</table>
<br />
      <select name="action" id="action" class="inl">
       <option value=""><?php __("-- Choose an action --"); ?></option>
                   <?php $action=array("DELETE" => "DELETE","REGENERATE"=>"REGENERATE"); eoption($action,"1");
        ?></select>

<input type="submit" class="inb" name="submit" value="<?php __("Validate"); ?>" />
</form>

	<?php
	    }

?>


<?php include_once("foot.php"); ?>
