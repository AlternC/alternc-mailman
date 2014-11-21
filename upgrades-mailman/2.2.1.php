<?php

   // We check that mysql php module is loaded 
if(!function_exists('mysql_connect'))  {
  if(!dl("mysql.so"))
    exit(1);
}

// we don't check our AlternC session
if(!chdir("/var/alternc/bureau"))
  exit(1);
require("/usr/share/alternc/panel/class/config_nochk.php");

$dbb= new DB_system();
$dbb2= new DB_system();
$dbb3= new DB_system();


// we go super-admin
$admin->enabled=1;
if(!$mailman->is_vhost_applied){
  #If we are dealing withnon virtual mailing lists
  $dbb->query("SELECT id from address where type='mailman';");
  $query=array();
  #We just add a mailbox without path and with delivery mailman to the address
  while($dbb->next_record()){
    $query[]="INSERT IGNORE INTO mailbox (id,address_id,path,delivery) values('',".$dbb->f('id').",'','mailman');";
  }
  foreach ($query as $q){
    $dbb->query($q);
  }
}else{
  #Here we are dealing with virtual lists
  $lst_functions=array('','-request','-owner','-admin','-bounces','-confirm','-join','-leave','-subscribe','-unsubscribe');
  $dbb->query("select list,domain,uid from mailman where list !='mailman';");
  while($dbb->next_record()){
    $domain=$dbb->f('domain');print_r($domain);
    $compte=$dbb->f('uid');
    $list=$dbb->f('list');
    $dbb2->query("SELECT id from domaines where domaine='".$domain."' and compte=$compte;");
    $dbb2->next_record();
    $dom_id=$dbb2->f('id');
    foreach($lst_functions as $ll){
      #First we create the virtual list addresses as they should : list-domain-function
      $name="$list-$domain$ll";
      $db->query("INSERT INTO address (domain_id, address,type) VALUES ($dom_id, '".addslashes($name)."','mailman');");
      $db->query("SELECT id from address where domain_id=$dom_id AND address='".$name."';");
      $db->next_record();
      $local_mid=$db->f('id');
      $m="$list$ll";print_r($m);echo "\n";
      $dbb3->query("SELECT id from address where address='".$m."' and domain_id=$dom_id ;");
      $dbb3->next_record();
      $m_id=$dbb3->f('id');
      $recipient="$name@$domain";
      # We add a mailbox without path and with delivery = mailman to the newly created address
      $db->query("INSERT INTO mailbox SET address_id=$local_mid, delivery='mailman';");
      #We specify that the newly created address is an alias of the pre-existing one. Hence list-function@domain is aliased by list-domain-function@domain
      $db->query("INSERT INTO recipient SET address_id=$m_id , recipients='".addslashes($recipient)."';");



    }
  }
}
#We regenerate the url to work with mpm-itk
$db->query("UPDATE mailman set url='".$L_FQDN."';");
?>
