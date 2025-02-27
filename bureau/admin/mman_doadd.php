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
 Purpose of file: Create a new mailing-list and ask for the values.
 ----------------------------------------------------------------------
*/

require_once("../class/config.php");

$fields = array (
	"domain"     => array ("request", "string", ""),
	"login"     => array ("request", "string", ""),
	"owner"     => array ("request", "string", ""),
);
getFields($fields);

if (!preg_match('/^\w[\w-]+$/', $login)) {
    $msg->raise("ERROR","mailman",_('Invalid list name (only letters, digits, underscores and hyphens).'));
	include("mman_add.php");
	exit();
}

$r=$mailman->add_lst($domain,$login,$owner);
if (!$r) {
	include("mman_add.php");
	exit();
} else {
	$msg->raise("INFO","mailman",_("The mailing list has been successfully created."));
	include("mman_list.php");
	exit();
}
?>
