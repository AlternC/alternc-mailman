<?php
/*
 $Id: mman_syncmembers.php 2714 2010-06-02 18:06:46Z benjamin $
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
 Purpose of file: Synchronize the members of a mailman list 
 with a textfield posted from a webform (can be used as an api too)
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");

$fields = array (
	"id"     => array ("request", "integer", ""),
	"members"     => array ("request", "string", ""),
);
getFields($fields);

$members=trim($members);
header("content-type: text/plain");
header("content-disposition: attachment; filename=syncresult.txt");

if (!$members) {
  echo "No Action : member list is empty !\n";
}

$mailman->syncmembers($id,$members);

?>
