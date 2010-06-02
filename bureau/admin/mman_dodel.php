<?php
/*
 $Id: mman_dodel.php 2 2003-06-13 20:30:40Z root $
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
	"confirm"     => array ("request", "integer", ""),
	"cancel"     => array ("request", "integer", ""),
	"d"    => array ("request", "array", ""),
);
getFields($fields);

$stchange = (!$quota->cancreate("mailman"));

if ($cancel)
{
	include ("mman_list.php");
	exit();
}

if (!is_array($d))
{
	$tmp = array($d);
	$d = $tmp;
}
reset($d);

if ($confirm)
{
	foreach ($d as $id)
	{
		$r = $mailman->delete_lst($id);
		if (!$r)
		{
			$error .= $err->errstr() . "<br />";
		}
		else
		{
			$error .= sprintf(_("The list %s has been successfully deleted."), $r) . "<br />";
		}
	}
}

include ("mman_list.php");
exit();

?>