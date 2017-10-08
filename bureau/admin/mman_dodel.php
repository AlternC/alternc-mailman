<?php
/*
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2000-2012 by the AlternC Development Team.
 https://alternc.org/a
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
	"confirm"     => array ("request", "string", ""),
	"cancel"     => array ("request", "string", ""),
	"d"    => array ("request", "array", array()),
);
getFields($fields);

$stchange = (!$quota->cancreate("mailman"));

if ($cancel) {
  include ("mman_list.php");
  exit();
}

if (!is_array($d)) {
  $tmp = array($d);
  $d = $tmp;
}
reset($d);

if ($confirm) {
  foreach ($d as $id) {
    $r = $mailman->delete_lst($id);
    if ($r) {
        $msg->raise("INFO","mailman",_("The list %s has been successfully deleted."), $r);
    }
  }
}

include ("mman_list.php");
exit();

?>