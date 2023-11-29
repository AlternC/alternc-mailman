<?php
/*
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2000-2021 by the AlternC Development Team.
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
 Purpose of file: ask for confirmation to migrate to mailman3
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");
include_once("head.php");

$fields = array (
		 "id"      => array ("request", "integer", ""),
		 "consent" => array ("request", "string", ""),
		 );
getFields($fields);
$lst = $mailman->get_lst($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($consent === 'on') {
    if ($lst) {
        $r = $mailman->migrate($id);
        if ($r) {
            $msg->raise("INFO","mailman",_("The mailing list will be migrated in a few minutes."));
            include("mman_list.php");
            exit();
        } else {
            $msg->raise("INFO","mailman",_("There was an error, please contact your administrator"));
        }
    }
  }
}

if (!($me=$lst)) {
  ?>
	  <h3><?php __("Mailing lists"); ?></h3>
<?php
    echo $msg->msg_html_all();
 include_once("foot.php");
 exit();
}

?>
<h3><?php __("Mailing lists"); ?></h3>
<hr/>
<?php
echo $msg->msg_html_all();

?>
<form method="post" action="mman_migrate.php?id=<?= $id ?>" name="main" id="main">
<?php csrf_get(); ?>
<input type="hidden" name="id" value="<?= $id ?>" />
<?php echo "<h3>".sprintf(_("Migration of list %s to mailman3"),$me)."</h3>"; ?>
<table class="tedit">
<tr><th><label for="pass"><?php __("I consent to the risk of losing data"); ?> </label></th><td>
	<input type="checkbox" id="consent" name="consent" />
	</td></tr>
<tr><td colspan="2">
  <input type="submit" class="inb" name="submit" value="<?php __("Go"); ?>"/>
</td></tr>
</table>
  </form>

<script type="text/javascript">
  $(document).ready(function() {
      $('#consent').focus();
    });
</script>

<?php include_once("foot.php"); ?>
