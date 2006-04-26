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
 Original Author of file:  Benjamin Sonntag, Franck Missoum
 Purpose of file: Left frame : Managing Mailing-lists WITH MAILMAN 2.1
 ----------------------------------------------------------------------
*/

/* ############# ML ############# */
$q=$quota->getquota("mailman");
if ($q["t"]>0) { 
?>
<tr><td nowrap>
<a href="mman_list.php"><?php __("Mailing lists"); ?></a><br>
 <small><?php __("(with mailman)"); ?></small><br>
</td></tr>
<?php
}
 ?>
