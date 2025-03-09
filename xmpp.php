<?php
/* 
	** Notify From XMPP Account.
	** Author: Rainer Dohmen
	** after an idea from: Pedram Asbaghi (Ponishweb.ir).
	** Special Thanks From Taha Shieenavaz.
*/

if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

/** Hooks einbinden **/
$plugins->add_hook('datahandler_login_complete_end', 'my_login_notifications');
$plugins->add_hook('newthread_do_newthread_end','my_thread_notifications');
$plugins->add_hook('member_do_register_end','my_signup_notifications');
$plugins->add_hook('calendar_do_addevent_end','my_calendar_notifications');
$plugins->add_hook('admin_login_success', 'my_adminpanel_enter_notifications');
$plugins->add_hook('modcp_end','my_modcp_enter_notifications');

function xmpp_info(){
	return array(
		'name'				=> 'XMPP Benachrichtigungen',
		'description'		=> 'Werde über die neuesten Ereignisse in Deinem Forum per XMPP informiert',
		'author'				=> 'dora71',
		'version'			=> '1.0',
		'guid'				=> '',
		'codename' 			=> 'xmpp',
		'compatibility'	=> '18*',
		'website'			=>	'https://github.com/dora71/mybb-xmpp-plugin',
		'authorsite'		=>	''
		);
}

function xmpp_install(){
	global $db, $mybb;

	$setting_group = array(
	    'name' => 'my_xmpp_settings',
	    'title' => 'XMPP Benachrichtigungseinstellungen',
	    'description' => 'XMPP Benachrichtigungs Plugin',
	    'disporder' => 1,
	    'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);


	$setting_array = array(
	    'my_xmpp_sender' => array(
	        'title' => 'XMPP Senderadresse',
	        'description' => 'Absenderadresse, von der aus Benachrichtigungen verschickt werden sollen',
	        'optionscode' => 'text',
	        'value' => 'absender@meinserver.tld',
	        'disporder' => 1
	    ),'my_xmpp_passwd' => array(
	        'title' => 'Passwort für die Absenderadresse',
	        'description' => "Konto sollte ausschliesslich für das MyBB Forum angelegt werden",
	        'optionscode' => 'text',
	        'value' => 'topsecret',
	        'disporder' => 2
	    ),'my_xmpp_chat' => array(
	        'title' => 'XMPP Empfänger',
	        'description' => "Adresse des Benachrichtigungsempfängers",
	        'optionscode' => 'text',
	        'value' => 'empfaenger@server.tld',
	        'disporder' => 3
	    ),'my_xmpp_muc' => array(
	        'title' => 'XMPP Empfaengergruppe',
	        'description' => "Adresse der Gruppe, welche die Benachrichtigungen erhalten soll (MUC)",
	        'optionscode' => 'text',
	        'value' => 'gruppe@conference.server.tld',
	        'disporder' => 4
	    ),'my_xmpp_muc_alias' => array(
	        'title' => 'Aliasname',
	        'description' => "Welcher Absendername soll in der Gruppe erscheinen?",
	        'optionscode' => 'text',
	        'value' => 'BoardAdmin',
	        'disporder' => 4	        
	    ),'my_xmpp_login_status' => array(
	        'title' => 'Benachrichtigung beim Login erhalten?',
	        'description' => 'Falls aktiviert, werden Nachrichten bei jedem Login versendet',
	        'optionscode' => 'yesno',
	        'value' => 1,
	        'disporder' => 5
	    ),'my_xmpp_signup_status' => array(
	        'title' => 'Benachrichtigung bei Registrierung erhalten?',
	        'description' => 'Falls aktiviert, werden Nachrichten bei Neuregistrierung versendet',
	        'optionscode' => 'yesno',
	        'value' => 1,
	        'disporder' => 6
	    ),'my_xmpp_thread_status' => array(
	        'title' => 'Benachrichtigung bei neuem Thema?',
	        'description' => 'Falls aktiviert, werden Nachrichten bei Erstellung eines neuen Themas versendet',
	        'optionscode' => 'yesno',
	        'value' => 1,
	        'disporder' => 7
	    ),'my_xmpp_calendar_status' => array(
	        'title' => 'Benachrichtigung bei neuem Kalendereintrag?',
	        'description' => 'Falls aktiviert, werden Nachrichten bei einem neuen Kalendereintrag versendet',
	        'optionscode' => 'yesno',
	        'value' => 1,
	        'disporder' => 7	        
	    ),'my_xmpp_security_status' => array(
	    	  'title' => 'Sicherheitsbenachrichtigungen aktivieren?',
	        'description' => 'Benachrichtigung bei Zutritt zum AdminCP oder ModCP',
	        'optionscode' => 'yesno',
	        'value' => 1,
	        'disporder' => 8
	    ),'my_xmpp_thread2muc' => array(
	        'title' => 'Neue Themen als Benachrichtigung in einen MUC?',
	        'description' => 'Soll bei neuen Themen eine Gruppe oder eine Einzelperson benachrichtigt werden?',
	        'optionscode' => "select\n0=Einzelperson\n1=MUC",
	        'value' => 0,
	        'disporder' => 9
	    )
		);

		foreach($setting_array as $name => $setting)
		{
		    $setting['name'] = $name;
		    $setting['gid'] = $gid;

		    $db->insert_query('settings', $setting);
		}
		
		rebuild_settings();
	
}

function xmpp_is_installed()
{
	global $mybb;
	if(isset($mybb->settings['my_xmpp_sender']))
	{
	    return true;
	}
	return false;
}

function xmpp_uninstall()
{
	global $db;
	$db->delete_query('settings', "name IN ('my_xmpp_sender','my_xmpp_passwd','my_xmpp_chat','my_xmpp_muc','my_xmpp_muc_alias','my_xmpp_signup_status','my_xmpp_login_status','my_xmpp_thread_status','my_xmpp_security_status','my_xmpp_thread2muc','my_xmpp_calendar_status')");
	$db->delete_query('settinggroups', "name = 'my_xmpp_settings'");
	rebuild_settings();
}

function xmpp_activate(){}
function xmpp_deactivate(){}

function my_login_notifications($obj){
	global $mybb;
	if(!$mybb->settings['my_xmpp_login_status']){return FALSE;}
	$data = get_object_vars($obj);
	$login_message = "Benutzer ".$data['login_data']['username']." hat sich ins Forum eingeloggt\n".$mybb->settings['bburl'];
	/** Senderoutine mit Nachricht $login_message **/
	sendXMPPMsg($login_message,0);
}

function my_thread_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_xmpp_thread_status']){return FALSE;}
	$ThreadQuery = $db->query("SELECT subject,username,tid FROM ".TABLE_PREFIX."threads ORDER BY tid DESC LIMIT 1");
	$LastThread = $db->fetch_array($ThreadQuery);
	$thread_message = "Ein neues Thema mit dem Titel ".$LastThread['subject']." wurde von ".$LastThread['username']." begonnen.\n".$mybb->settings['bburl']."/showthread.php?tid=".$LastThread['tid'];
	if($mybb->settings['my_xmpp_thread2muc'] == 1) {
		/** Senderoutine mit $thread_message in MUC **/
		sendXMPPMsg($thread_message,1);
	} else {
		/** Senderoutine mit $thread_message in Chat **/
		sendXMPPMsg($thread_message,0);
	}
}

function my_signup_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_xmpp_signup_status']){return FALSE;}
	$LastUserQuery = $db->query('SELECT username FROM '.TABLE_PREFIX.'users ORDER BY uid DESC LIMIT 1');
	$LastUser = $db->fetch_array($LastUserQuery);
	$signup_message = $LastUser['username']." hat sich erfolgreich im Forum registriert.\n".$mybb->settings['bburl'];
	/** Senderoutine mit $signup_message **/
	sendXMPPMsg($signup_message,0);
}

function my_adminpanel_enter_notifications()
{
    global $mybb;
    if(!$mybb->settings['my_xmpp_security_status']){return FALSE;}
    date_default_timezone_set('Europe/Berlin');    
    // Benutzerinformationen
    $uid = $mybb->user['uid'];
    $username = $mybb->user['username'];
    $ip_address = get_ip();
    $time = date('H:i',TIME_NOW);
    $adminentry_message = "Login in's AdminPanel von Benutzer ".$username." (UID: ".$uid.")\nIP: ".$ip_address."\num ".$time." Uhr\n".$mybb->settings['bburl'];
    sendXMPPMsg($adminentry_message,0);
}

function my_modcp_enter_notifications()
{
    global $mybb;
    if(!$mybb->settings['my_xmpp_security_status']){return FALSE;}
    date_default_timezone_set('Europe/Berlin');    
    // Benutzerinformationen
    $uid = $mybb->user['uid'];
    $username = $mybb->user['username'];
    $ip_address = get_ip();
    $time = date('H:i',TIME_NOW);
    $adminentry_message = "Login in's ModCP von Benutzer ".$username." (UID: ".$uid.")\nIP: ".$ip_address."\num ".$time." Uhr\n".$mybb->settings['bburl'];
    sendXMPPMsg($adminentry_message,0);
}

function my_calendar_notifications(){
	global $db,$mybb;
	if(!$mybb->settings['my_xmpp_calendar_status']){return FALSE;}
	$EventQuery = $db->query("SELECT uid,eid,name,private FROM ".TABLE_PREFIX."events ORDER BY eid DESC LIMIT 1");
	$LastEvent = $db->fetch_array($EventQuery);
	if ($LastEvent['private'] == 1){return FALSE;}
	$UserQuery = $db->query("SELECT username FROM ".TABLE_PREFIX."users WHERE uid=".$LastEvent['uid']);
	$LastUser = $db->fetch_array($UserQuery);
	$event_message = "Ein Kalendereintrag mit dem Titel\n*".$LastEvent['name']."* wurde von ".$LastUser['username']." erstellt:\n".$mybb->settings['bburl']."/calendar.php?action=event&eid=".$LastEvent['eid'];
	if($mybb->settings['my_xmpp_thread2muc'] == 1) {
		/** Senderoutine mit $thread_message in MUC **/
		sendXMPPMsg($event_message,1);
	} else {
		/** Senderoutine mit $thread_message in Chat **/
		sendXMPPMsg($event_message,0);
	}
}

function sendXMPPMsg($msg,$muc) {
	global $mybb;
	$sender = $mybb->settings['my_xmpp_sender'];
	$pass = $mybb->settings['my_xmpp_passwd'];
	$target = $mybb->settings['my_xmpp_chat'];
	$targetmuc = $mybb->settings['my_xmpp_muc'];
	$alias = $mybb->settings['my_xmpp_muc_alias'];
	if($muc == 0) {
		$command = 'echo "'.$msg.'" | /usr/bin/go-sendxmpp -u '.$sender.' -p '.$pass.' '.$target;
		exec($command, $output, $retval);
	}
	elseif($muc == 1) {
		$command = 'echo "'.$msg.'" | /usr/bin/go-sendxmpp -c -a '.$alias.' -u '.$sender.' -p '.$pass.' '.$targetmuc;
		exec($command, $output, $retval);
	}
}

?>