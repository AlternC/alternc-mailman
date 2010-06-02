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
 Purpose of file: Create a new mailing-list and ask for the values.
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");

$fields = array (
	"domain"     => array ("request", "string", ""),
	"login"     => array ("request", "string", ""),
	"owner"     => array ("request", "string", ""),
	"pass"     => array ("request", "string", ""),
	"pass2"     => array ("request", "string", ""),
);
getFields($fields);

$r=$mailman->add_lst($domain,$login,$owner,$pass,$pass2);
if (!$r) {
	$error=$err->errstr();
	include("mman_add.php");
	exit();
} else {
	$error=_("The mailing list has been successfully created.");
	include("mman_list.php");
	exit();
}
?>
