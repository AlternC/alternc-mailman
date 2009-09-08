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
 Purpose of file: Delete mailing-lists
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");

$fields = array (
	"d"    => array ("request", "array", ""),
);
getFields($fields);
if (!is_array($d)) {
        $d[]=$d;
}
reset($d);


$stchange=(!$quota->cancreate("mailman"));

$error="";

if (empty($d))
{
	include ("mman_list.php");
	exit();
}

include("head.php");

?>
</head>
<body>
<h3><?php __("Deleting mailman lists"); ?></h3>
<p><?php __("Please confirm the deletion of the following mailman lists:"); ?></p>
<form action="mman_dodel.php" method="post">
<?php

foreach ($d as $id)
{
	echo "<input type=\"hidden\" name=\"d[]\" value=\"" . $id . "\" />";
	echo $mailman->get_lst($id) . "<br />";
}

?>
<p><input type="submit" class="inb" name="confirm" value="<?php __("Delete the selected mailman lists"); ?>" /> - <input type="submit" name="cancel" id="cancel" class="inb" value="<?php __("Don't delete lists and go back to the mailman list"); ?>" />
</p>
</form>

<p>
<?php __("Warning: Deleting a mailman list will destroy all the subscribed users, preferences and archives it contains! You will <b>NOT</b> be able to get your data back!"); ?>
</p>


</body>
</html>
