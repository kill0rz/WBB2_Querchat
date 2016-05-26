<html>
<head>
<meta charset="UTF-8" />
<title>Querchat Client Konfiguration</title>
</head>
<body>
<?php

require('./global.php');
require('./acp/lib/class_parse.php');
require('./acp/lib/class_parsecode.php');	
require('./acp/lib/options.inc.php');	

if(!($wbbuserdata['userid'] == 1)){
	header("Location: ./index.php");
	exit();
}

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

?>Hallo <?php echo $wbbuserdata['username']; ?> ! <a href="./index.php">Zur&uuml;ck zum Forum</a><hr><?php

if(isset($_POST['del'])){
	$db->query("DELETE FROM bb".$n."_querchat_config");
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

if($isconfigured){
	echo "Das Forum ist mit folgenden Parametern konfiguriert:<br>";
	echo "URL zur Serverdatei: ".$URL."<br>";
	echo "Authhash: ".$authhash."<br>";
	echo "Board-ID: ".$boardid."<br>";
	echo "<form action='querchat_config.php' method='post'><input type='hidden' name='del' value='del' /><input type='submit' value='Diese Konfiguration wieder l&ouml;schen!'/></form>";
	if(isset($_GET['action']) and $_GET['action'] == "verify"){
		$site = getsite($URL,"mode=test&authhash={$authhash}&boardid={$boardid}");
		if($site == "very ok"){
			$islive = true;
			$db->unbuffered_query("UPDATE bb".$n."_querchat_config SET islive = '1'");
		}else{
			echo "<font color='red'>Es gab einen Fehler! Entweder konnte der Server auf der Gegenseite nicht erreicht werden oder deine Konfiguration ist fehlerhaft (Auch auf Leerezeichen am Anfang und am Ende achten!).</font><hr>";
		}
	}
	if($islive){
		echo "<font color='green'>Die Verbindung ist verifiziert!</font><br>
		Du kannst den Chat nun nutzen.";
	}else{
		echo "Die Serververbindung ist noch nicht verifiziert. Deshlab funktioniert der Chat noch nicht.<br>
			 <a href='./querchat_config.php?action=verify'>Verifiziere jetzt!</a>";
	}
}else{
	if(isset($_POST['url']) and isset($_POST['id']) and isset($_POST['hash']) and trim($_POST['id']) != '' and trim($_POST['url']) != '' and trim($_POST['hash']) != ''){
		if($db->query("INSERT INTO bb".$n."_querchat_config SET `boardid`='".addslashes($_POST['id'])."', `URL`='".addslashes(trim($_POST['url']))."',`id`='".addslashes(trim($_POST['id']))."',`authhash`='".addslashes(trim($_POST['hash']))."',`islive`='".addslashes("false")."'")){
			echo "<font color='green'>Erfolg!</font> Das Forum wurde erfolgreich eingetragen<br><a href='".$_SERVER['PHP_SELF']."'>Bitte lade die Seite neu!<a>";
		}
	}else{
		?>
		Querchat ist noch keinem Server zugeordnet.<br>
		Um Querchat nutzen zu k&ouml;nnen, muss der Hack seine Informationen von einem Querchat-Server laden.<br>
		Server und Client k&ouml;nnen grundsätzlich gleichzeitig auf dem Server laufen. Es kann immer nur ein Server gleichzeitig eingetragen werden!<br>
		<br>
		Kontaktiere bitte denjenigen, der den Server aufgesetzt hat und lass dir folgende Informationen geben: ID, Authhash und URL zur querchat_server.php.<br>
		Trage diese Informationen unten ein und schicke das Formular ab. Danach kannst du sofort loslegen.<br>
		Beachte bitte, dass alle Informationen auf dem Rechner gespiechert werden, auf dem die Serversoftware l&auml;uft. Hier werden also keine Chatprotokolle gespeichert!<br>
		<br>
		<hr>
		<h2>Einen Server hinzuf&uuml;gen</h2>
		<form action="querchat_config.php" method="post">
		ID: <input type="text" name="id" /><br>
		Authentifizierungsstring: <input type="text" name="hash" /><br>
		URL zur querchat_server.php: <input type="text" name="url" /><br>
		<input type="submit" />
		</form>
		<hr>
		<?php
	}
}