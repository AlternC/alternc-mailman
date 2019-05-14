#!/usr/bin/php
<?php
/* 
	remove in django_db (mailman3web) and in mailman3_db (mailman)
	the domain in parameter.
*/


$domain = $argv[1];


$file = "/etc/alternc/my.cnf";
if (!$settings = parse_ini_file($file)){ 
	throw new exception('Unable to open ' . $file . '.');
}


// ------------------   MailmanWeb db  ------------------//

$dbname = 'mailman3web';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//django domain
$query = $db->prepare("DELETE FROM django_mailman3_maildomain WHERE mail_domain = ?;");
$ok = $query->execute(array($domain));
if ( $ok ){
	echo "[remove] domaine => $domain from mailman3-web";
}


// ------------------   Mailman db  ------------------//

$dbname = 'mailman';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

$query = $db->prepare("DELETE FROM domain WHERE mail_host = ?;");
$ok = $query->execute( array($domain) );
if ( $ok ){
	echo "[remove] domaine => $domain from mailman3";
}
