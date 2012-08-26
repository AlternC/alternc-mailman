<?php
/*
 ----------------------------------------------------------------------
 AlternC - Web Hosting System
 Copyright (C) 2000-2012 by the AlternC Development Team.
 https://alternc.org/
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
 Purpose of file: Manage mailing-lists with Mailman
 ----------------------------------------------------------------------
*/

class m_mailman {


  /* ----------------------------------------------------------------- */
  /** Dummy function for translation texts.
   */
  function _cron_result() {
    // We set the cron available result message here 
    // so that gettext see them ;) 
    array(
	  _("This list already exist"),
	  _("This list does not exist"),
	  _("A fatal error happened when creating the list"),
	  _("A fatal error happened when deleting the list"),
	  _("A fatal error happened when changing the list password"),
	  _("A fatal error happened when getting the list url"),
	  );
  }


  /* ----------------------------------------------------------------- */
  /** Return the mailing-lists managed by this member:
   * @param $domain string The domain's list we want (or null to prevent filtering on a specific domain)
   * @param $order_by array how do we sort the lists (default is domain then listname)
   * @return array an ordered array of associative arrays with all the members lists
   */
  function enum_ml($domain = null, $order_by = array('domain', 'list')) {
    global $err,$db,$cuid;
    $err->log("mailman","enum_ml");
    $order_by = array_map("addslashes", $order_by);
    $order = 'ORDER BY `' . join('`,`', $order_by) . '`';
    $query = "SELECT * FROM mailman WHERE uid=$cuid".
      (is_null($domain) ? "" : " AND domain='" . addslashes($domain) ."'" ) .
      " $order;";
    $db->query($query);
    if (!$db->num_rows()) {
      $err->raise("mailman",_("No list defined yet"));
      return array();
    }
    $mls=array();
    while ($db->next_record()) {
      $mls[]=$db->Record;
    }
    return $mls;
  }
  

  /* ----------------------------------------------------------------- */
  /** Count mailing list for a user
   * @param $uid integer The uid of the user we want info about
   */
  function count_ml_user($uid) {
    global $db,$err,$cuid;
    $db->query("SELECT COUNT(*) AS count FROM mailman WHERE uid='{$uid}';");
    if ($db->next_record()) {
      return $db->f('count');
    } else {
      return 0;
    }
  }


  /* ----------------------------------------------------------------- */
  /** Return the list of domains that may be used by mailman for the current account
   * @return array an array of domain names 
   */
  function prefix_list() {
    global $db,$err,$cuid;
    $r=array();
    $db->query("SELECT domaine FROM domaines WHERE compte='$cuid' AND gesmx = 1 ORDER BY domaine;");
    while ($db->next_record()) {
      $r[]=$db->f("domaine");
    }
    return $r;
  }


  /* ----------------------------------------------------------------- */
  /** Echoes a select list options of the list of domains that may be used 
   * by mailman for the current account. 
   * @param $current string the item that will be selected in the list
   * @return array an array of domain names 
   */
  function select_prefix_list($current) {
    global $db,$err;
    $r=$this->prefix_list();
    reset($r);
    while (list($key,$val)=each($r)) {
      if ($current==$val) $c=" selected=\"selected\""; else $c="";
      echo "<option$c>$val</option>";
    }
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** Get all the informations for a list
   * @param $id integer is the list id in alternc's database.
   * @return array an associative array with all the list informations
   * or false if an error occured.
   */
  function get_lst($id) {
    global $db, $err, $cuid;
    $err->log("mailman","get_list", $cuid);
    
    $q = "SELECT * FROM mailman WHERE uid = '" . $cuid . "' && id = '" . $id . "'";
    $db->query($q);
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",_("This list does not exist"));
      return false;
    }
    $login = $db->f("list");
    $domain = $db->f("domain");
    return $login . "@" . $domain;
  }


  /* ----------------------------------------------------------------- */
  /** Add a mail in 'address' table for mailman delivery
   * @param $login string the left part of the @ for the list email 
   * @param $dom_id the domain-ID on which the list will be attached
   * @param $function the function of that wrapper (owner, post, bounce ...)
   * @param $list the mailman list name
   * @return boolean TRUE if the wrapper has been created, or FALSE if an error occured
   */
  private function add_wrapper($login,$dom_id,$function,$list) {
    global $db;
    // TODO: TEST THIS, I'M NOT SURE IT'S ENOUGH TO DEFINE IT THAT WAY !!
    $db->query("INSERT INTO address SET type='mailman', delivery='mailman', address='".addslashes($login)."', domain_id=$dom_id;");
    return true;    
  }


  /* ----------------------------------------------------------------- */
  /** Delete a mail in 'address' table for mailman delivery
   * @param $login string the left part of the @ for the list email 
   * @param $dom_id integer the domain-ID on which the list will be attached
   * @return boolean TRUE if the wrapper has been deleted, or FALSE if an error occured
   */
  private function del_wrapper($login,$dom_id) {
    global $db;
    $db->query("DELETE FROM address WHERE type='mailman' AND address='".addslashes($login)."' AND domain_id=$dom_id;");
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** Create a new list for this member:
   * @param $domain string the domain name on which the list will be attached
   * @param $login string the left part of the @ for the list email 
   * @param $owner the email address of the list administrator (required)
   * @param $password the initial list password (required)
   * @return boolean TRUE if the list has been created, or FALSE if an error occured
   */
  function add_lst($domain,$login,$owner,$password,$password2) {
    global $db,$err,$quota,$mail,$cuid,$dom;
    $err->log("mailman","add_lst",$login."@".$domain." - ".$owner);
    /* the list' internal name */
    $login = strtolower($login);
    if (!filter_var($login."@".$domain,FILTER_VALIDATE_EMAIL)) {
      $err->raise("mailman",_("The email you entered is syntaxically incorrect"));
      return false;
    }

    if (!($dom_id=$dom->get_domain_byname($domain))) {
      return false;
    }

    if (file_exists("/usr/share/alternc-mailman/patches/mailman-true-virtual.applied")) {
      $name = $login . '-' . $domain;
    } else {
      $name = $login;
    }

    if ($login=="") {
      $err->raise("mailman",_("The login (left part of the @) is mandatory"));
      return false;
    }
    if (!$owner || !$password) {
      $err->raise("mailman",_("The owner email and the password are mandatory"));
      return false;
    }
    if (checkmail($owner)) {
      $err->raise("mailman",_("This email is incorrect"));
      return false;
    }
    if ($password!=$password2) {
      $err->raise("mailman",12);
      return false;
    }
    $r=$this->prefix_list();
    if (!in_array($domain,$r) || $domain=="") {
      $err->raise("mailman",_("This domain does not exist."));
      return false;
    }
    $db->query("SELECT COUNT(*) AS cnt FROM mailman WHERE name='$name';");
    $db->next_record();
    if ($db->f("cnt")) {
      $err->raise("mailman",_("A list with the same name already exist on the server. Please choose another name."));
        return false;
    }
    // Prefix OK, let's check that all emails wrapper we will create are unused
    if (!$mail->available($login."@".$domain) ||
	!$mail->available($login."-request@".$domain) ||
	!$mail->available($login."-owner@".$domain) ||
	!$mail->available($login."-admin@".$domain) ||
	!$mail->available($login."-bounces@".$domain) ||
	!$mail->available($login."-confirm@".$domain) ||
	!$mail->available($login."-join@".$domain) ||
	!$mail->available($login."-leave@".$domain) ||
	!$mail->available($login."-subscribe@".$domain) ||
	!$mail->available($login."-unsubscribe@".$domain)) {
      // This is a mail account already !!!
      $err->raise("mailman",_("This email address (or one of the list-subscribe, list-unsubscribe etc.) are already used."));
      return false;
    }
    // Check the quota
    if ($quota->cancreate("mailman")) {
      // List creation : 1. insert into the DB
      $db->query("INSERT INTO mailman (uid,list,domain,name,mailman_action) VALUES ('$cuid','$login','$domain','$name','CREATE');");
      if (!$this->add_wrapper($login,$dom_id,"post",$name) ||
	  !$this->add_wrapper($login."-request",$dom_id,"request",$name) ||
	  !$this->add_wrapper($login."-owner",$dom_id,"owner",$name) ||
	  !$this->add_wrapper($login."-admin",$dom_id,"admin",$name) ||
	  !$this->add_wrapper($login."-bounces",$dom_id,"bounces",$name) ||
	  !$this->add_wrapper($login."-confirm",$dom_id,"confirm",$name) ||
	  !$this->add_wrapper($login."-join",$dom_id,"join",$name) ||
	  !$this->add_wrapper($login."-leave",$dom_id,"leave",$name) ||
	  !$this->add_wrapper($login."-subscribe",$dom_id,"subscribe",$name) ||
	  !$this->add_wrapper($login."-unsubscribe",$dom_id,"unsubscribe",$name)
	  ) {
	// didn't work : rollback
	$this->del_wrapper($login,$dom_id);	        $this->del_wrapper($login."-request",$dom_id);
	$this->del_wrapper($login."-owner",$dom_id);	$this->del_wrapper($login."-admin",$dom_id);
	$this->del_wrapper($login."-bounces",$dom_id);	$this->del_wrapper($login."-confirm",$dom_id);
	$this->del_wrapper($login."-join",$dom_id);	$this->del_wrapper($login."-leave",$dom_id);
	$this->del_wrapper($login."-subscribe",$dom_id);	$this->del_wrapper($login."-unsubscribe",$dom_id);
	$db->query("DELETE FROM mailman WHERE name='$name';");
	return false;
      }
      return true;
    } else {
      $err->raise("mailman",_("Your mailing-list quota is over, you cannot create more mailing-lists.")); // quota
      return false;
    }
  }


  /* ----------------------------------------------------------------- */
  /** Delete a mailing-list
   * @param $id integer the id number of the mailing list in alternc's database
   * @return boolean TRUE if the list has been deleted or FALSE if an error occured
   */
  function delete_lst($id) {
    global $db,$err,$mail,$cuid;
    $err->log("mailman","delete_lst",$id);
    // We delete lists only in the current member's account.
    $db->query("SELECT * FROM mailman WHERE id=$id and uid='$cuid';");
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",_("This list does not exist"));
      return false;
    }
    if ($db->f("mail_action")!='OK') {
      $err->raise("mailman",_("This list has pending action, you cannot delete it"));
      return false;
    }
    $login=$db->f("list");
    $domain=$db->f("domain");

    $db->query("UPDATE MAILMAN SET mailman_action='DELETE' WHERE id=$id");
    $this->del_wrapper($login,$domain);	        $this->del_wrapper($login."-request",$domain);
    $this->del_wrapper($login."-owner",$domain);	$this->del_wrapper($login."-admin",$domain);
    $this->del_wrapper($login."-bounces",$domain);	$this->del_wrapper($login."-confirm",$domain);
    $this->del_wrapper($login."-join",$domain);	$this->del_wrapper($login."-leave",$domain);
    $this->del_wrapper($login."-subscribe",$domain);	$this->del_wrapper($login."-unsubscribe",$domain);
    return $login."@".$domain;
  }


  /* ----------------------------------------------------------------- */
  /** Echoes the list's members as a text file, one subscriber per line.
   * 
   *  Assumes that you are using the Mailman multi-domain patch,
   *  but will fallback to check only the list name (without domain)
   *  to support also installations without the patch.
   *
   * @param $id integer The list whose members we want to dump
   * @return void : this function ECHOES the result !
   */
  /** FIXME: this function has no equivalent in cron mode, remove this */
  function members($id) {
    global $err,$db,$cuid;
    $err->log("mailman","members");
    $db->query("SELECT CONCAT(list, '-', domain) as list FROM mailman WHERE uid='$cuid' AND id='$id';");
                          
    if (!$db->num_rows()) {
      // fallback
      $db->query("SELECT list FROM mailman WHERE uid='$cuid' AND id='$id';");

      if (!$db->num_rows()) {
        $err->raise("mailman",_("No list defined yet"));
        return false;
      }
    }

    $db->next_record();
    passthru("/usr/lib/alternc/mailman.list ".$db->Record["list"]);
  }


  /* ----------------------------------------------------------------- */
  /** Synchronize the list's members from a text file, one subscriber per line.
   * 
   *  Assumes that you are using the Mailman multi-domain patch,
   *  but will fallback to check only the list name (without domain)
   *  to support also installations without the patch.
   *
   * @param $id integer The list whose members we want to dump
   * @return void : this function ECHOES the result !
   */
  /** FIXME: this function has no equivalent in cron mode, remove this */
  function syncmembers($id,$members) {
    global $err,$db,$cuid;
    $err->log("mailman","members");
    $db->query("SELECT CONCAT(list, '-', domain) as list FROM mailman WHERE uid='$cuid' AND id='$id';");
                          
    if (!$db->num_rows()) {
      // fallback
      $db->query("SELECT list FROM mailman WHERE uid='$cuid' AND id='$id';");

      if (!$db->num_rows()) {
        $err->raise("mailman",_("No list defined yet"));
        return false;
      }
    }

    $db->next_record();
    passthru("/usr/lib/alternc/mailman.list ".$db->Record["list"]);
  }


  /* ----------------------------------------------------------------- */
  /** Change the mailman administrator password of a list
   * @param $id integer The list number in alternc's database
   * @param $pass string The new password
   * @param $pass2 string The new password (confirmation)
   * @return boolean TRUE if the password has been changed or FALSE if an error occured.
   */
 function passwd($id,$pass,$pass2) {
   global $db,$err,$mail,$cuid;
    $err->log("mailman","passwd",$id);

    $db->query("SELECT * FROM mailman WHERE id=$id and uid='$cuid';");
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",_("This list does not exist"));
      return false;
    }
    if ($pass!=$pass2) {
      $err->raise("mailman",_("The passwords are differents, please try again"));
      return false;
    }
    $login=$db->f("list");
    $domain=$db->f("domain");
    $db->query("UPDATE mailman SET mailman_action='PASSWORD', password='".addslashes($pass)."' WHERE id=$id;");
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** Returns the current url for $list administration
   * @param $list integer the list for which we want the url
   * @return string the url (starting by http or https) or false if an error occured
   */
  /** FIXME: this function has no equivalent in cron mode, remove this */
  function get_list_url($list) {
    global $db,$err,$cuid;
    $q = "SELECT * FROM mailman WHERE uid = '" . $cuid . "' && id = '" . intval($list) . "'";
    $db->query($q);
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",_("This list does not exist"));
      return false;
    }
    $list=$db->Record["name"];
    unset($out);
    $exec="/usr/lib/alternc/mailman.geturl ".escapeshellarg($list);
    exec($exec,$out,$ret);
    if ($ret) return false;
    if ($out[0]) 
      return $out[0]; 
    else 
      return false;
  }


  /* ----------------------------------------------------------------- */
  /** Set the management url for $list 
   * @param $list integer the list for which we want to change the url
   * @param $url string the url, MUST be either http:// or https:// + domain + /cgi-bin/mailman/
   * @return boolean TRUE if the url has been changes
   */
  function set_list_url($list,$newurl) {
    global $db,$err,$cuid;
    $q = "SELECT * FROM mailman WHERE uid = '" . $cuid . "' && id = '" . intval($list) . "'";
    $db->query($q);
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",_("This list does not exist"));
      return false;
    }
    $list=$db->Record["name"];
    unset($out);
    $db->query("UPDATE mailman SET mailman_action='SETURL', url='".addslashes($newurl)."' WHERE id=$id;");
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** Quota name
   */
  function hook_quota_names() {
    return array("mailman"=>_("Mailing lists (mailman)"));
  }


  /* ----------------------------------------------------------------- */
  /** This function is a hook who is called each time a domain is uninstalled
   * in an account (or when we select "gesmx = no" in the domain panel.)
   * @param string $dom_id Domaine to delete
   * @return boolean TRUE if the domain has been deleted from mailman
   * @access private
   */
  function hook_dom_del_mx_domain($dom_id) {
    global $err,$dom;
    $err->log("mailman","del_dom",$dom_id);
    $domain=$dom->get_domain_byid($dom_id);
    $listes=$this->enum_ml($domain);
    while (list($key,$val)=each($listes)) {
      $this->delete_lst($val["id"]);
    }
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** Returns the quota for the current account as an array
   * @param $name string The quota name we get (should always be "mailman" for this class
   * @return array an array with used (key 'u') and totally available (key 't') quota for the current account.
   * or FALSE if an error occured
   * @access private
   */ 
  function hook_quota_get($name) {
    global $err,$cuid,$db;
    if ($name=="mailman") {
      $db->query("SELECT COUNT(*) AS cnt FROM mailman WHERE uid='$cuid';");
      $db->next_record();
      return $db->f("cnt");
    } else return false;
  }
  


} /* Class m_mailman */

