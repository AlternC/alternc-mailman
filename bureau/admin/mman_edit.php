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
 Purpose of file: Delete mailing-lists
 ----------------------------------------------------------------------
*/

require_once("../class/config.php");

$fields = array (
	"d"    => array ("request", "array", ""),
	"action"    => array ("request", "string", ""),
);
getFields($fields);
if (!is_array($d)) {
        $d[]=$d;
}
reset($d);

$stchange=(!$quota->cancreate("mailman"));

$error="";

if (empty($d)) {
  include ("mman_list.php");
  exit();
}

include("head.php");

?>
</head>
<body>
  <h3><?php __("Editing mailman lists"); ?></h3>
<hr id="topbar"/>
<br />
<p><?php __("Please confirm you want to $action the following mailman lists:"); ?></p>
<form action="mman_doedit.php" method="post">
<?php

foreach ($d as $id) {
  echo "<input type=\"hidden\" name=\"d[]\" value=\"" . $id . "\" />";
  echo $mailman->get_lst($id) . "<br />";
}

  echo "<input type=\"hidden\" name=\"action\" value=\"" . $action . "\" />";
?>
<p><input type="submit" class="inb" name="confirm" value="<?php __("$action the selected mailman lists"); ?>" /> - <input type="submit" name="cancel" id="cancel" class="inb" value="<?php __("Don't $action lists and go back to the mailman list"); ?>" />
</p>
</form>
<?php if($action=="DELETE"){ ?>
<p>
<?php __("Warning: Deleting a mailman list will destroy all the subscribed users, preferences and archives it contains! You will <b>NOT</b> be able to get your data back!"); ?>
</p>
<?php } ?>

</body>
</html>
