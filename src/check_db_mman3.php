#!/usr/bin/php
<?php
/* 
	check in django_db and in mailman3_db 
	if list, account in alternc exist and 
	remove if account do not exist.
*/


$file = "/etc/alternc/my.cnf";
if (!$settings = parse_ini_file($file)){ 
	throw new exception('Unable to open ' . $file . '.');
}

// ------------------   alternc db  ------------------//

$dbname = 'alternc';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//users
$query = $db->query("SELECT username FROM mailman_account;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll(PDO::FETCH_COLUMN);
	foreach($tmp as $user){
		$users_alternc[] = $user;
	}
}

//list
$query = $db->query("SELECT list, domain FROM mailman;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll();//PDO::FETCH_COLUMN);

	foreach($tmp as $list){
		$lists_alternc[$list['domain']][] = $list['list'];
	}
}
//---------------------------------------------------//


// ------------------   MailmanWeb db  ------------------//

$dbname = 'mailman3web';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//django user
$query = $db->query("SELECT username FROM auth_user;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll(PDO::FETCH_COLUMN);
	foreach($tmp as $user){
		$users[] = $user;
	}
}

//------------------------------------------------------//


// ------------------   Mailman db  ------------------//

$dbname = 'mailman';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

$query = $db->query("SELECT list_name, mail_host FROM mailinglist;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll();//PDO::FETCH_COLUMN);
	foreach($tmp as $list){
		$lists[$list['mail_host']][] = $list['list_name'];
	}
}

//---------------------------------------------------//


//check
$users_to_remove = array();
foreach($users_alternc as $user){
	if(!in_array($user,$users)){
		$users_to_remove[] = $user;
	}
}
$lists_to_remove = array();
var_dump($lists_alternc);
foreach($lists_alternc as $domain => $array_list){
	foreach($array_list as $list){
		if(!in_array($list,$lists[ $domain ])){
			$lists_to_remove[$domain][] = $list;
		}
	}
}
//--endcheck


//remove
$dbname = 'alternc';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//remove users
if(count($users_to_remove)>0){
	$questionmarks = str_repeat("?,", count($users_to_remove)-1) . "?"; //va mettre 1 à n '?' dans le string
	$request = $db->prepare("DELETE FROM mailman_account WHERE username in ($questionmarks);");
	$ok = $request->execute( $users_to_remove );//$users_to_remove is yet an array !!
	if ( $ok ){ echo("all this users are remove"); foreach($users_to_remove as $u){ echo "[$u] "; }}
}

//remove lists
if(count($lists_to_remove)>0){
	foreach($lists_to_remove as $domain => $array_list){
		foreach($array_list as $list){

			$request = $db->prepare("DELETE FROM mailman WHERE domain=? and list=?");
			$ok = $request->execute( array($domain,$list) );

			$request = $db->prepare("DELETE FROM address WHERE address LIKE ? and type='mailman' and domaine_id=
							(SELECT id FROM domaines dom WHERE domaine=? ); ");
			$ok = $ok && $request->execute( array($list."%",$domain) );
			if ( $ok ){ echo("this lists are remove: $list from domain $domain"); }
		}
	}
	
}
