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
 Original Author of file: Benjamin Sonntag
 Purpose of file: Manage mailing-lists with Mailman
 ----------------------------------------------------------------------
*/

class m_mailman {


  /* ----------------------------------------------------------------- */
  /**
   * Quota name
   */
  function alternc_quota_names() {
    return "mailman";
  }


  /* ----------------------------------------------------------------- */
  /**
   * Return the mailing-lists managed by this member :
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
      $err->raise("mailman",1);
      return array();
    }
    $mls=array();
    while ($db->next_record()) {
      $mls[]=$db->Record;
    }
    return $mls;
  }
  

  /* ----------------------------------------------------------------- */
  /**
   * Return the list of domains that may be used by mailman for the current account
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
  /**
   * Echoes a select list options of the list of domains that may be used 
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
  /**
   * Get all th informations for a list
   * @param $id integer is the list id in alternc's database.
   * @return array an associative array with all the list informations
   * or false if an error occured.
   */
  function get_lst($id)
  {
    global $db, $err, $cuid;
    $err->log("mailman","get_list", $cuid);
    
    $q = "SELECT * FROM mailman WHERE uid = '" . $cuid . "' && id = '" . $id . "'";
    $db->query($q);
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",9);
      return false;
    }
    $login = $db->f("list");
    $domain = $db->f("domain");
    return $login . "@" . $domain;
  }
  


  /* ----------------------------------------------------------------- */
  /**
   * Create a new list for this member :
   * @param $domain string the domain name on which the list will be attached
   * @param $login string the left part of the @ for the list email 
   * @param $owner the email address of the list administrator (required)
   * @param $password the initial list password (required)
   * @return boolean TRUE if the list has been created, or FALSE if an error occured
   */
  function add_lst($domain,$login,$owner,$password,$password2) {
    global $db,$err,$quota,$mail,$cuid;
    $err->log("mailman","add_lst",$login."@".$domain." - ".$owner);
    /* the list' internal name */
    $login = strtolower($login);
    if (!checkloginmail($login)) {
      $err->raise("mailman",8);
      return false;
    }

    if (file_exists("/usr/share/alternc-mailman/patches/mailman-true-virtual.applied")) {
      $name = $login . '-' . $domain;
    } else {
      $name = $login;
    }

    if ($login=="") {
      $err->raise("mailman",2);
      return false;
    }
    if (!$owner || !$password) {
      $err->raise("mailman",3);
      return false;
    }
    if (checkmail($owner)) {
      $err->raise("mailman",4);
      return false;
    }
    if ($password!=$password2) {
      $err->raise("mailman",12);
      return false;
    }
    $r=$this->prefix_list();
    if (!in_array($domain,$r) || $domain=="") {
      $err->raise("mailman",5);
      return false;
    }
    $db->query("SELECT COUNT(*) AS cnt FROM mailman WHERE name='$name';");
    $db->next_record();
    if ($db->f("cnt")) {
        $err->raise("mailman",10);
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
      $err->raise("mailman",6);
      return false;
    }
    // Check the quota
    if ($quota->cancreate("mailman")) {
      // List creation : 1. insert into the DB
      $db->query("INSERT INTO mailman (uid,list,domain,name) VALUES ('$cuid','$login','$domain','$name');");
      if (!$mail->add_wrapper($login,$domain,"/var/lib/mailman/mail/mailman post $name","mailman") ||
	  !$mail->add_wrapper($login."-request",$domain,"/var/lib/mailman/mail/mailman request $name","mailman") ||
	  !$mail->add_wrapper($login."-owner",$domain,"/var/lib/mailman/mail/mailman owner $name","mailman") ||
	  !$mail->add_wrapper($login."-admin",$domain,"/var/lib/mailman/mail/mailman admin $name","mailman") ||
	  !$mail->add_wrapper($login."-bounces",$domain,"/var/lib/mailman/mail/mailman bounces $name","mailman") ||
	  !$mail->add_wrapper($login."-confirm",$domain,"/var/lib/mailman/mail/mailman confirm $name","mailman") ||
	  !$mail->add_wrapper($login."-join",$domain,"/var/lib/mailman/mail/mailman join $name","mailman") ||
	  !$mail->add_wrapper($login."-leave",$domain,"/var/lib/mailman/mail/mailman leave $name","mailman") ||
	  !$mail->add_wrapper($login."-subscribe",$domain,"/var/lib/mailman/mail/mailman subscribe $name","mailman") ||
	  !$mail->add_wrapper($login."-unsubscribe",$domain,"/var/lib/mailman/mail/mailman unsubscribe $name","mailman")
	  ) {
	// didn't work : rollback
	$mail->del_wrapper($login,$domain);	        $mail->del_wrapper($login."-request",$domain);
	$mail->del_wrapper($login."-owner",$domain);	$mail->del_wrapper($login."-admin",$domain);
	$mail->del_wrapper($login."-bounces",$domain);	$mail->del_wrapper($login."-confirm",$domain);
	$mail->del_wrapper($login."-join",$domain);	$mail->del_wrapper($login."-leave",$domain);
	$mail->del_wrapper($login."-subscribe",$domain);	$mail->del_wrapper($login."-unsubscribe",$domain);
	$db->query("DELETE FROM mailman WHERE name='$name';");
	return false;
      }
      // Wrapper created, sql ok, now let's create the list :)
      exec("/usr/lib/alternc/mailman.create ".escapeshellarg($login."@".$domain)." ".escapeshellarg($owner)." ".escapeshellarg($password)."", &$output, &$return);
      if ($return) {
        $err->raise("mailman", "failed to create mailman list. error: %d, output: %s", $return, join("\n", $output));
      }
      return !$return;
    } else {
      $err->raise("mailman",7); // quota
      return false;
    }
  }


  /* ----------------------------------------------------------------- */
  /**
   * Delete a mailing-list
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
      $err->raise("mailman",9);
      return false;
    }
    $login=$db->f("list");
    $domain=$db->f("domain");

    if (file_exists("/usr/share/alternc-mailman/patches/mailman-true-virtual.applied")) {
      exec("/usr/lib/alternc/mailman.delete ".escapeshellarg($login.'@'.$domain), &$output, &$return);
    } else {
      exec("/usr/lib/alternc/mailman.delete ".escapeshellarg($login), &$output, &$return);
    }

    if ($return) {
      $err->raise("mailman", "failed to delete mailman list. error: %d, output: %s", $return, join("\n", $output));
      return false;
    }
    $db->query("DELETE FROM mailman WHERE id=$id");
    $mail->del_wrapper($login,$domain);	        $mail->del_wrapper($login."-request",$domain);
    $mail->del_wrapper($login."-owner",$domain);	$mail->del_wrapper($login."-admin",$domain);
    $mail->del_wrapper($login."-bounces",$domain);	$mail->del_wrapper($login."-confirm",$domain);
    $mail->del_wrapper($login."-join",$domain);	$mail->del_wrapper($login."-leave",$domain);
    $mail->del_wrapper($login."-subscribe",$domain);	$mail->del_wrapper($login."-unsubscribe",$domain);
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
  function members($id) {
    global $err,$db,$cuid;
    $err->log("mailman","members");
    $db->query("SELECT CONCAT(list, '-', domain) as list FROM mailman WHERE
uid='$cuid' AND id='$id';");
                          
    if (!$db->num_rows()) {
      // fallback
      $db->query("SELECT list FROM mailman WHERE uid='$cuid' AND id='$id';");

      if (!$db->num_rows()) {
        $err->raise("mailman",1);
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
      $err->raise("mailman",9);
      return false;
    }
    if ($pass!=$pass2) {
      $err->raise("mailman",11);
      return false;
    }
    $login=$db->f("list");
    $domain=$db->f("domain");

    if (file_exists("/usr/share/alternc-mailman/patches/mailman-true-virtual.applied")) {
      exec("/usr/lib/alternc/mailman.passwd ".escapeshellarg($login.'@'.$domain)." ".escapeshellarg($pass), &$output, &$return);
    } else {
      exec("/usr/lib/alternc/mailman.passwd ".escapeshellarg($login)." ".escapeshellarg($pass), &$output, &$return);
    }
    return true;
  }


  /* ----------------------------------------------------------------- */
  /** This function is a hook who is called each time a domain is uninstalled
   * in an account (or when we select "gesmx = no" in the domain panel.)
   * @param string $dom Domaine to delete
   * @return boolean TRUE if the domain has been deleted from mailman
   * @access private
   */
  function alternc_del_mx_domain($dom) {
    global $err;
    $err->log("mailman","del_dom",$dom);

    $listes=$this->enum_ml($dom);
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
  function alternc_get_quota($name) {
    global $err,$cuid,$db;
    if ($name=="mailman") {
      $db->query("SELECT COUNT(*) AS cnt FROM mailman WHERE uid='$cuid';");
      $db->next_record();
      return $db->f("cnt");
    } else return false;
  }



} /* Class m_mailman */

?>
