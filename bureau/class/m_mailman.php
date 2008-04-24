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
  function m_mailman() {
  }

  /* ----------------------------------------------------------------- */
  /**
   * Quota name
   */
  function alternc_quota_names() {
    return "mailman";
  }

  /*****************************************************************************/
  /** Return the mailing-lists managed by this member : */
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

  /*****************************************************************************/
  function prefix_list() {
    global $db,$err,$cuid;
    $r=array();
    $db->query("SELECT domaine FROM domaines WHERE compte='$cuid' AND gesmx = 1 ORDER BY domaine;");
    while ($db->next_record()) {
      $r[]=$db->f("domaine");
    }
    return $r;
  }
  /*****************************************************************************/
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

  /*****************************************************************************/
	/** Get list informations */
	function get_lst($id)
	{
		global $db, $err, $cuid;
		$err->log("mailman","get_list", $cuid);

		$q = "SELECT * FROM mailman WHERE uid = '" . $cuid . "' && id = '" . $id . "'";
		$db->query($q);
		$db->next_record();
		if (!$db->f("id"))
		{
			$err->raise("mailman",9);
			return false;
		}
		$login = $db->f("list");
		$domain = $db->f("domain");
		return $login . "@" . $domain;
	}

  /*****************************************************************************/
  /** Create a new list for this member : */
  function add_lst($domain,$login,$owner,$password) {
    global $db,$err,$quota,$mail,$cuid;
    $err->log("mailman","add_lst",$login."@".$domain." - ".$owner);
    /* the list' internal name */
    $login = strtolower($login);
    $name = $login . '-' . $domain;

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
    // Prefixe OK, on verifie la non-existence des mails que l'on va créer...
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
    // Le compte n'existe pas, on vérifie le quota et on le créé.
    if ($quota->cancreate("mailman")) {
      // Creation de la liste : 1. recherche du nom de la liste
      // CA NE MARCHE PAS !
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
	$mail->del_wrapper($login,$domain);	        $mail->del_wrapper($login."-request",$domain);
	$mail->del_wrapper($login."-owner",$domain);	$mail->del_wrapper($login."-admin",$domain);
	$mail->del_wrapper($login."-bounces",$domain);	$mail->del_wrapper($login."-confirm",$domain);
	$mail->del_wrapper($login."-join",$domain);	$mail->del_wrapper($login."-leave",$domain);
	$mail->del_wrapper($login."-subscribe",$domain);	$mail->del_wrapper($login."-unsubscribe",$domain);
	$db->query("DELETE FROM mailman WHERE name='$name';");
	return false;
      }
      // Wrapper created, sql ok, now let's create the list :)
      exec("/usr/lib/alternc/mailman.create \"".escapeshellcmd($login."@".$domain)."\" \"".escapeshellcmd($owner)."\" \"".escapeshellcmd($password)."\"", &$output, &$return);
      if ($return) {
        $err->raise("mailman", "failed to create mailman list. error: %d, output: %s", $return, join("\n", $output));
      }
      return !$return;
    } else {
      $err->raise("mailman",7); // quota
      return false;
    }
  }

  /*****************************************************************************/
  function delete_lst($id) {
    global $db,$err,$mail,$cuid;
    $err->log("mailman","delete_lst",$id);

    $db->query("SELECT * FROM mailman WHERE id=$id and uid='$cuid';");
    $db->next_record();
    if (!$db->f("id")) {
      $err->raise("mailman",9);
      return false;
    }
    $login=$db->f("list");
    $domain=$db->f("domain");
    exec("/usr/lib/alternc/mailman.delete ".escapeshellarg($db->f("name").'@'.$domain), &$output, &$return);
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
  /** Returns the list's members as a text file, one subscriber per
   *   line.
   */
 function members($id) {
    global $err,$db,$cuid;
    $err->log("mailman","members");
    $db->query("SELECT * FROM mailman WHERE uid='$cuid' AND id='$id';");
    if (!$db->num_rows()) {
      $err->raise("mailman",1);
      return false;
    }
    $db->next_record();
    passthru("/usr/lib/alternc/mailman.list ".$db->Record["list"]);
  }


  /* ----------------------------------------------------------------- */
  /** Fonction appellée par domaines lorsqu'un domaine est effacé.
   * Cette fonction efface tous les comptes mails du domaine concerné.
   * @param string $dom Domaine à effacer
   * @return boolean TRUE si le domaine a bien été effacé, FALSE si une erreur s'est produite.
   * @access private
   */
  function alternc_del_mx_domain($dom) {
    global $err;
    $err->log("mailman","del_dom",$dom);

    // Suppression des listes du domaine
    $listes=$this->enum_ml($dom);
    while (list($key,$val)=each($listes)) {
      $this->delete_lst($val["id"]);
    }
    return true;
  }

  /* ----------------------------------------------------------------- */
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
