#!/usr/bin/php
<?php
/* 
	synchronise email between mailman3 and alternc
*/


$file = "/etc/alternc/my.cnf";
if (!$settings = parse_ini_file($file)){ 
	throw new exception('Unable to open ' . $file . '.');
}

// ------------------   alternc db  ------------------//

$dbname = 'alternc';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//users
$query = $db->query("SELECT username, email FROM mailman_account;");
$ok = $query->execute();
if ( $ok ){
	$users_alternc = $query->fetchAll();
}


// ------------------   MailmanWeb db  ------------------//

$dbname = 'mailman3web';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//django user
$query = $db->query("SELECT username, email FROM auth_user;");
$ok = $query->execute();
if ( $ok ){
	$users = $query->fetchAll();
}

//check to update
$toUpdate = array();
foreach($users_alternc as $user){
	foreach($users as $u){
		if($u['username'] == $user['username']){
			if($u['email'] != $user['email']){
				$us['username'] = $u['username'];
				$us['email'] = $u['email'];					
				$toUpdate[] = $us;
			}
		}
	}
}

//update
$dbname = 'alternc';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);
foreach($toUpdate as $u){
	$query = $db->prepare("UPDATE mailman_account SET email = ? WHERE username = ?;");
	$ok = $query->execute( array($u['email'],$u['username']) );
	$query = $db->prepare("UPDATE mailman m SET owner = ? WHERE  m.uid in (SELECT ma.uid FROM mailman_account ma WHERE ma.username = ?);");
	$ok = $ok && $query->execute( array($u['email'],$u['username']) );
}
