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
 Original Author of file: Benjamin Sonntag, Franck Missoum
 Purpose of file: Show the Mailing-Lists owned by the current user
 ----------------------------------------------------------------------
*/
require_once("../class/config.php");

include("head.php");
// If there is no installed domain, let's failed definitely !
if (count($dom->enum_domains())==0) {
  $error=_("No domain is installed on your account, you cannot create any mailing list!");
  ?>
        </head>
        <body>
	    <h3><?php __("Mailing lists"); ?></h3>
	 <?php echo "<p class=\"error\">$error</p>"; ?>
        </body>
        </html>

        <?php
	    exit();
	 }

if(!$r=$mailman->enum_ml()) {
  $error.=$err->errstr();
	?>
	</head>
	<body>
	<h3><?php __("Mailing lists"); ?></h3>
	<?php echo "<p class=\"error\">$error</p>"; ?>
	</body>
	</html>

	<?php
}
else {
	?>
	</head>
	<body>
	    <h3><?php __("Mailing lists"); ?></h3>

 <?php if ($error) echo "<p class=\"error\">$error</p>"; ?>

	<form method="post" action="mman_del.php">
	<table cellspacing="0" cellpadding="4" border="1">
	<tr><th><?php __("Delete"); ?></th><th><?php __("List name"); ?></th><th colspan="4">&nbsp;</th></tr>
	<?php
	reset($r);
	$col=1;
	while (list($key,$val)=each($r)) {
		$col=3-$col;
		?>
		<tr class="lst<?php echo $col; ?>">
			<td align="center"><input type="checkbox" class="inc" name="d[]" value="<?php echo $val["id"]; ?>"></td>
			<td><?php echo $val["list"]."@".$val["domain"] ?></td>
			<td>&nbsp;<a href="https://<?php echo $val["domain"]; ?>/cgi-bin/mailman/listinfo/<?php echo $val["name"] ?>"><?php __("Public page"); ?></a>&nbsp;</td>
			<td>&nbsp;<a href="https://<?php echo $val["domain"]; ?>/cgi-bin/mailman/admin/<?php echo $val["name"] ?>"><?php __("List admin"); ?></a>&nbsp;</td>
			<td>&nbsp;<a href="https://<?php echo $val["domain"]; ?>/cgi-bin/mailman/admindb/<?php echo $val["name"] ?>"><?php __("Pending messages"); ?></a>&nbsp;</td>
			<td>&nbsp;<a href="mman_members.php?id=<?php echo $val["id"] ?>"><?php __("List Members"); ?></a>&nbsp;</td>
		</tr>
		<?php
		}
	?>
	<tr><td colspan="6" align="center"><input type="submit" class="inb" name="submit" value="<?php __("Delete the checked lists"); ?>"></td></tr>
	</table>
	</form>

	<?php
}

if ($quota->cancreate("mailman")) {
?>
	<a href="mman_add.php"><?php __("Create a list"); ?></a><br>
	<?php
}
?>
<p>
<ul>
<li>Mailman est un gestionnaire de liste de discussion/diffusion tr�s puissant. Il poss�de une interface web compl�te de gestion de liste, accessible par les diff�rents liens ci-dessus.</li>
<li>Mailman permet de mod�rer une liste : pour mod�rer les futurs abonn�s, aller dans "Options de confidentialit�"->"Filtres exp�diteur" puis choisissez "Oui" dans "Les envois des nouveaux abonn�s doivent �tre mod�r�s par d�faut?". En effet, mailman peut mod�rer CHAQUE abonn� d'une liste. </li>
<li>Pour mod�rer les NON-abonn�s d'une liste, aller dans "Options de confidentialit�"->"Filtres exp�diteur" puis choisissez parmi "Accepter En attente Rejeter Ignorer" pour "Action � entreprendre pour les envois des non-abonn�s"</li>
<li>De nombreuses autres options peuvent �tre modifi�es dans mailman. Pour toute information, cliquez sur le lien "D�tails de ..." en dessous de chaque �l�ment configurable, ou contactez-nous.</li>
</ul>
</p>

</body>
</html>
