<?php


require('./global.php');
require('./acp/lib/class_parse.php');
require('./acp/lib/class_parsecode.php');	
require('./acp/lib/options.inc.php');	

function execute($ho,$state,$array){ 
	global $cookies;
	global $proxy;
	global $proxyport;
	$log_hoster = $array;
	$ch = curl_init(); 

    $url = $log_hoster[$ho][0]; 
    $postdata = $log_hoster[$ho][1]; 
    $ref = $log_hoster[$ho][2]; 

    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); 
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); 
	curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	if($state == true){
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
	} else {
		curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
	}
    $retu = curl_exec($ch);
	sleep(1);
    return $retu;
}

function getsite($zielurl,$postdata = ""){
	$log_hoster = array( 
               array($zielurl,$postdata,$zielurl)
               ); 
			   
	$temp = execute(0,true,$log_hoster);
	
	return $temp;
}

$isconfigured = false;
$ergebnis = $db->query("SELECT * FROM bb".$n."_querchat_config");
while($row = $db->fetch_array($ergebnis)){
	$isconfigured = true;
	$boardid = $row['boardid'];
	$authhash = $row['authhash'];
	$URL = $row['URL'];
	$islive = $row['islive'];
}

if(!$isconfigured or !$islive){
	echo "Konnte keine Verbindung zum Server aufbauen.";
	exit();
}

//write
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="text" size="100" name="text" />
<input type="submit" value="Chat!" name="submit"/>
</form>
<hr>
<?php

if(isset($_POST['submit']) and isset($_POST['text']) and trim($_POST['text']) != '' and trim($_POST['submit']) == "Chat!"){
	if("post ok" == getsite($URL,"mode=write&authhash={$authhash}&boardid={$boardid}&userid={$wbbuserdata['userid']}&username={$wbbuserdata['username']}&inhalt={$_POST['text']}")) echo "<font color='green'>Erfolgreich eingetragen!</font><hr>";
	else echo "Konnte keine Verbindung zum Server aufbauen.<hr>";
	
	//$db->query("INSERT INTO bb".$n."_querchat_server_shouts SET boardid='".addslashes($_POST['boardid'])."',inhalt='".addslashes($_POST['inhalt'])."',autor_name='test',autor_id='".addslashes($_POST['userid'])."',time='".time()."'")
}

//read
if($site = getsite($URL,"mode=read&authhash={$authhash}&boardid={$boardid}")) echo $site;
else echo "Konnte keine Verbindung zum Server aufbauen.";