#!/usr/bin/php
<?php
/* 
	check in django_db and in mailman3_db 
	if list, account in alternc exist.
*/


$file = "/etc/alternc/my.cnf";
if (!$settings = parse_ini_file($file)){ 
	throw new exception('Unable to open ' . $file . '.');
}

// ------------------   alternc db  ------------------//

$dbname = 'alternc';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//users
$users_alternc = array();
$query = $db->query("SELECT username FROM mailman_account;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll(PDO::FETCH_COLUMN);
	foreach($tmp as $user){
		$users_alternc[] = $user;
	}
}

//list
$lists_alternc = array();
$query = $db->query("SELECT list, domain FROM mailman;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll();

	foreach($tmp as $list){
		$lists_alternc[$list['domain']][] = $list['list'];
	}
}
//---------------------------------------------------//


// ------------------   MailmanWeb db  ------------------//

$dbname = 'mailman3-web';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);


//django user
$users = array();
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

$dbname = 'mailman3';
$db = new pdo("mysql:host=".$settings['host'].";dbname=$dbname",$settings['user'],$settings['password']);

//mailman list
$lists = array();
$query = $db->query("SELECT list_name, mail_host FROM mailinglist;");
$ok = $query->execute();
if ( $ok ){
	$tmp = $query->fetchAll();
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
foreach($lists_alternc as $domain => $array_list){
	foreach($array_list as $list){
		if( ! empty($lists) ) {
			if(!in_array($list,$lists[ $domain ])){
				$lists_to_remove[$domain][] = $list;
			}
		}else{
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
	$questionmarks = str_repeat("?,", count($users_to_remove)-1) . "?"; //va mettre de 1 à n '?' dans le string
	$request = $db->prepare("DELETE FROM mailman_account WHERE username in ($questionmarks);");
	$request->execute( $users_to_remove );
}
//remove lists
if(count($lists_to_remove)>0){
	foreach($lists_to_remove as $domain => $array_list){
		foreach($array_list as $list){

			$request = $db->prepare("DELETE FROM mailman WHERE domain=? and list=?");
			$request->execute( array($domain,$list) );

			$request = $db->prepare("DELETE FROM address WHERE address LIKE ? and type='mailman' and domaine_id=
							(SELECT id FROM domaines dom WHERE domaine=? ); ");
			$request->execute( array($list."%",$domain) );
		}
	}
}
