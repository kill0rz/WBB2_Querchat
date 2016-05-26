<?php

@error_reporting(7);
$genstart=(float) array_sum(explode(' ', microtime()));
$phpversion = phpversion();

/** get function libary **/
require('./acp/lib/functions.php');
if (version_compare($phpversion, '4.1.0') == -1) {
	$_REQUEST = array_merge($HTTP_COOKIE_VARS, $HTTP_POST_VARS, $HTTP_GET_VARS);
	$_COOKIE =& $HTTP_COOKIE_VARS;
	$_SERVER =& $HTTP_SERVER_VARS;
	$_FILES =& $HTTP_POST_FILES;
	$_GET =& $HTTP_GET_VARS;
	$_POST =& $HTTP_POST_VARS;
}
// remove slashes in get post cookie data...
if (get_magic_quotes_gpc()) {
	if (count($_REQUEST)) $_REQUEST = stripslashes_array($_REQUEST);
	if (count($_POST)) $_POST = stripslashes_array($_POST);
	if (count($_GET)) $_GET = stripslashes_array($_GET);
	if (count($_COOKIE)) $_COOKIE = stripslashes_array($_COOKIE);
	if (count($_SERVER)) $_SERVER = stripslashes_array($_SERVER);
}

@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_sybase', '0');
/** connect db **/
require('./acp/lib/config.inc.php');
require('./acp/lib/class_db_mysql.php');

$db = &new db($sqlhost, $sqluser, $sqlpassword, $sqldb, $phpversion);

require('./acp/lib/class_parse.php');
require('./acp/lib/class_parsecode.php');	
require('./acp/lib/options.inc.php');	

$ausgabe = '';
//this file only accepts POST-REQUESTS by Querchat client

if(isset($_POST['boardid']) and isset($_POST['authhash'])){
	$ergebnis = $db->query("SELECT * FROM bb".$n."_querchat_server_boards WHERE ID = '".mysql_real_escape_string($_POST['boardid'])."'");
	while($db_boards = $db->fetch_array($ergebnis)){
		if($db_boards['authhash'] == $_POST['authhash']){
			if($_POST['mode'] == "read" and $db_boards['active'] == '1'){
				$zaehler = 0;
				$ergebnis2 = $db->query("SELECT * FROM bb".$n."_querchat_server_shouts ORDER BY time DESC");
				while($db_shouts = $db->fetch_array($ergebnis2)){
					$ergebnis3 = $db->query("SELECT * FROM bb".$n."_querchat_server_boards WHERE ID = '".mysql_real_escape_string($db_shouts['boardid'])."'");
					while($db_boards2 = $db->fetch_array($ergebnis3)){
						$ausgabe  .= "[ ".date("l | H:i", $db_shouts['time'])." ] <a href='".$db_boards2['url']."profile.php?userid=".trim($db_shouts['autor_id'])."' target='_blank'><b>".$db_boards2['prefix'].trim($db_shouts['autor_name'])."</b></a> ";
						$ausgabe .= $db_shouts['inhalt']."<br>\n";
						$zaehler++;
						if($zaehler >= 10) break;
					}
				}
			}elseif($_POST['mode'] == "write" and isset($_POST['userid']) and isset($_POST['username']) and isset($_POST['inhalt']) and $db_boards['active'] == '1'){
				$inhalt = htmlentities($_POST['inhalt']);
				$inhalt = str_replace("[b]","<b>",$inhalt);
				$inhalt = str_replace("[/b]","</b>",$inhalt);
				$inhalt = str_replace("[u]","<u>",$inhalt);
				$inhalt = str_replace("[/u]","</u>",$inhalt);
				$inhalt = str_replace("[i]","<i>",$inhalt);
				$inhalt = str_replace("[/i]","</i>",$inhalt);
				
				if(str_replace("http://","",$inhalt) != $inhalt){ 
					 $startstr = strpos($inhalt,"http://"); 
					 $endstr = strpos($inhalt," ",$startstr); 
					 if($endstr > 1){ 
						$length = $endstr - $startstr; 
					 }else{ 
						$length = 100000; 
					 } 
					 $url = substr($inhalt,$startstr,$length); 

					 $inhalt = str_replace($url,"<a href='".trim($url)."' target='_blank'>".trim($url)."</a>",$inhalt); 
				}
				if($db->query("INSERT INTO bb".$n."_querchat_server_shouts SET boardid='".addslashes(intval($_POST['boardid']))."',inhalt='".addslashes($inhalt)."',autor_name='".addslashes($_POST['username'])."',autor_id='".addslashes(intval($_POST['userid']))."',time='".time()."'")){
					$ausgabe = "post ok";
				}
			}elseif($_POST['mode'] == "test" and $db_boards['active'] == '1'){
				$ausgabe = "very ok";
			}else exit();
		}else exit();
    }
}else exit();

echo $ausgabe;