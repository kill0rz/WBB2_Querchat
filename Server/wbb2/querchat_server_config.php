<?php

require('./global.php');
require('./acp/lib/class_parse.php');
require('./acp/lib/class_parsecode.php');	
require('./acp/lib/options.inc.php');	

if(!($wbbuserdata['userid'] == 1)){
	header("Location: ./index.php");
	exit();
}

?>
<html>
<head>
<meta charset="UTF-8" />
<title>Querchat Server Konfiguration</title>
</head>
<body>
Hallo <?php echo $wbbuserdata['username']; ?> ! <a href="./index.php">Zur&uuml;ck zum Forum</a><br><br>
<h2>Ein Board zum Server hinzuf&uuml;gen</h2>
<form action="querchat_server_config.php" method="post">
Name des Forums: <input type="text" name="name" /><br>
URL zum Forum: <input type="text" name="url" /><br>
Pr&auml;fix (z.B. board1_ wird zu board1_Username) : <input type="text" name="prefix" /><br>
<input type="submit" />
</form>
<hr>
<?php


if(isset($_POST['name']) and isset($_POST['url']) and isset($_POST['prefix'])){
	$hash = md5(time().trim($_POST['name']));
	$url = str_replace("http://","",$_POST['url']);
	$url = "http://".$url;
	if(!(substr($url,-1) == "/")){
		$url .= "/";
	}
	if($db->query("INSERT INTO bb".$n."_querchat_server_boards SET `boardname`='".addslashes($_POST['name'])."', `active`='1', `url`='".addslashes($url)."',`prefix`='".addslashes($_POST['prefix'])."',`authhash`='".addslashes($hash)."'")){
		echo "Board erfolgreich eingetragen!<br>";
		echo "Gib die Daten f&uuml;r das Forum aus der Tabelle unten an den Client weiter (ID und Authentifizierungsstring).<br>";
		echo "Und auch die URL zur querchat_server.php (vermutlich <a href='http://".$_SERVER['HTTP_HOST'].str_replace("_config","",$_SERVER['REQUEST_URI'])."' target='_blank'>http://".$_SERVER['HTTP_HOST'].str_replace("_config","",$_SERVER['REQUEST_URI'])."</a>)<br><hr>";
	}
}

if(isset($_POST['del'])){
	if($db->query("UPDATE bb".$n."_querchat_server_boards SET active = 0 WHERE ID=".intval($_POST['del']))){
		echo "Board erfolgreich gel&ouml;scht!<hr>";
	}
}

echo "<h2> eingetragene Boards</h2>";
echo "<table border=2>";
echo "<tr><th>ID</th><th>Name</th><th>URL</th><th>Authentifizierungsstring</th><th>Pr&auml;fix</th><th>L&ouml;schen</th></tr>";
$ergebnis = $db->query("SELECT * FROM bb".$n."_querchat_server_boards WHERE active=1");
while($db_boards = $db->fetch_array($ergebnis)){
	echo "<tr><td>{$db_boards['ID']}</td><td>{$db_boards['boardname']}</td><td>{$db_boards['url']}</td><td>{$db_boards['authhash']}</td><td>{$db_boards['prefix']}</td><td><form action='querchat_server_config.php' method='post'><input type='hidden' name='del' value='{$db_boards['ID']}' /><button type='submit'><img src='images/del.gif'></button></form></td><tr>";
}