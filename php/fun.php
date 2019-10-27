<?php

// -------------------------------------------------------------------

$WEBROOTMIN = $_SERVER['DOCUMENT_ROOT']."/pySandboxEditor/files";

// -------------------------------------------------------------------

function checkDirPath($path) {
	$path = str_replace("..","",$path);
	global $WEBROOTMIN;
	$minpathlen = strlen($WEBROOTMIN);
	if (strlen($path) >= $minpathlen) {
		$pathstart = substr($path,0,$minpathlen);
		if ( $pathstart == $WEBROOTMIN ) {
			return $path;
		}
	}
	return $WEBROOTMIN;
}

function sanitizeFileName($file) {
	$file = preg_replace("/[^a-zA-Z0-9-\/_ \.]/", "", ($file));;
	$file = preg_replace("/\.\.+/", ".", $file);
	return $file;
}


function isLocal() {
	return (substr($_SERVER['HTTP_HOST'],0,9)=="localhost");
}

function rmdirRF($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? rmdirRF("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  } 

function get_client_ip() {
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


function mailHTML($to, $from, $subject, $message)
{
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
//	$headers .= 'To: ' . $to . "\r\n";
	$headers .= 'From: ' . $from . "\r\n";
	mail($to, $subject, $message, $headers);
}


function emailify($email) {
	$ea = explode("@",$email);
	if (count($ea)==2) {
		$r = "";
		$ds = "";
		for ($i=0;$i<strlen($email);$i++) {
//			$ds .= $email[$i];
			$ds .= "<span>" . $email[$i] . "</span>";
		}
		$r = "<a href='javascript:;' class='emasked' onmouseover=\"unmaskEmails(this);\">" . $ds . "</a>";
		return $r;
	}
	else {
		return $email;
	}
}


function getDateTimeForMySQL() {
	$datetime = time();
	$r = date("Y-m-d G:i:s", $datetime);
	return $r;
}


function leadDigits($n,$d) {
	$r = ("".$n);
	while ( (strlen($r) < $d) && (strlen($r) < 10) ) {
		$r = "0" . $r;
	}
	return $r;
}



function dbMultiQuery($queries)
{
	$db = jetDatabaseCredentials();
	$dbServer = $db->Server;
	$dbDatabase = $db->Database;
	$dbUsername = $db->Username;
	$dbPassword = $db->Password;

	$dbLink = new mysqli($dbServer, $dbUsername, $dbPassword, $dbDatabase);
	$dbLink->autocommit(FALSE);
	foreach($queries as $query) {
		$dbLink->query($query);
	}
	$dbLink->rollback();
	$dbLink->close();
	return true;
}


function dbQuery($query)
{
	$cr = dbCreds();

	$dbServer = $cr->Server;
	$dbHost = $cr->Host;
//	$dbPort = $cr->Port;
	$dbDatabase = $cr->Database;
	$dbUsername = $cr->Username;
	$dbPassword = $cr->Password;
	$dsn = 'mysql:dbname='.$dbDatabase.';host='.$dbHost; //.';port='.$dbPort;
	$db = new PDO($dsn, $dbUsername, $dbPassword);
	$query = trim($query);
	$q = array("numrows"=>0, "results"=>array(), "id"=>0);
	if (strtolower(substr($query,0,6))=="select") {
		$s = $db->query($query);
		if (!empty($s)) {
			$q['numrows'] = $s->rowCount();
			$q['results'] = $s->fetchAll(PDO::FETCH_ASSOC);
		} else {
//			print "\n\nEMPTY\n\n";
		}
	}
	else if (strtolower(substr($query,0,6))=="insert") {
		$s = $db->prepare($query);
		$s->execute();
//		$q['numrows'] = $s->rowCount();
		$q['id'] = $db->lastInsertId();
	}
	else if (strtolower(substr($query,0,6))=="update") {
		$s = $db->prepare($query);
		$s->execute();
		$q['numrows'] = $s->rowCount();
	}
	else if (strtolower(substr($query,0,6))=="delete") {
		$s = $db->prepare($query);
		$s->execute();
		$q['numrows'] = $s->rowCount();
	}
	else {
		$s = $db->prepare($query);
		$s->execute();
	}
//	print $query . "\n\n";
	return $q;
}



function _dbQuery($query)
{

	$db = jetDatabaseCredentials();
	$dbServer = $db->Server;
	$dbDatabase = $db->Database;
	$dbUsername = $db->Username;
	$dbPassword = $db->Password;

	$query = trim($query);
	$dbLink = mysqli_connect($dbServer, $dbUsername, $dbPassword) or die ('I cannot connect to the database.');
	mysqli_select_db($dbLink,$dbDatabase);
	$q = array();
	$q['id'] = 0;
	
	if (strtolower(substr($query,0,6))=="select")
	{
		$q['results'] = mysqli_query($dbLink,$query);	
		$q['numrows'] = 0;
		if ($q['results']!=null)
		{
			$q['numrows'] = mysqli_num_rows($q['results']);
		}
	}
	else if (strtolower(substr($query,0,12))=="show columns")
	{
		$q['results'] = mysqli_query($dbLink,$query);	
		$q['numrows'] = 0;
		if ($q['results']!=null)
		{
			$q['numrows'] = mysqli_num_rows($q['results']);
		}
	}
	else if (strtolower(substr($query,0,6))=="insert")
	{
		mysqli_query($dbLink,$query);
		$q['id'] = mysqli_insert_id($dbLink);
	}
	else if (strtolower(substr($query,0,6))=="update")
	{
		mysqli_query($dbLink,$query);
		$q['affected'] = mysqli_affected_rows($dbLink);
	}
	else if (strtolower(substr($query,0,6))=="delete")
	{
		mysqli_query($dbLink,$query);
		$q['affected'] = mysqli_affected_rows($dbLink);
	}
	else
	{
		mysqli_query($dbLink,$query);
	}
	
	mysqli_close($dbLink);
	return $q;
}




function dbDrawTable($q,$border=1) {
	if ($q['numrows']>0) {
		$first = true;
		print "<table border='$border'>";
		while($r=mysql_fetch_array($q['results'])) {
			$arraykeys = array_keys($r);
			$indexes = array();
			foreach ($arraykeys as $arraykey) {
				if (!(is_numeric($arraykey))) {
					$indexes[] = $arraykey;
				}
			}
			if ($first) {
				print "<tr><th>";
				print implode("</th><th>",$indexes);
				print "</th></tr>";
				$first = false;
			}
			print "<tr><td>";	
			$cells = array();
			foreach ($indexes as $index) {
				$cells[] = $r[$index];
			}
			print implode("</td><td>",$cells);
			print "</td></tr>";
		}
		print "</table>";
	}
}

function generateRandomString($n)
{
	$r = "";
	$abc = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$m = strlen($abc) - 1;
	for ($i=0;$i<$n;$i++)
	{
		$ri = rand(0,$m);
		$r .= "".$abc[$ri];
	}
	return $r;
}

function grabNum($v)
{
	$r = grabVar($v);
	if (!(is_numeric($r))) { $r = 0; }
	return $r;
}



function grabVar($v)
{
	$r = "";
	if (isset($_GET[$v])) { $r = $_GET[$v]; }
	if (isset($_POST[$v])) { $r = $_POST[$v]; }
	$ts = "".$r;
	if (str_replace("'","",$ts)!=$ts)
	{
		$r = str_replace("'","&#39;",$r);
	}
	return $r;
}
	

function grabCurl($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$r = curl_exec($curl);
	curl_close($curl);
	return $r;
}




?>