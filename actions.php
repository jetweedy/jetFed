<?php
ini_set("display_errors", 1);
include("php/fun.php");
header("Content-type:text/plain");

function sanitizeFileName($file) {
	$file = preg_replace("/[^a-zA-Z0-9-\/_ \.]/", "", strtolower($file));;
	$file = preg_replace("/\.\.+/", ".", $file);
	return $file;
}

if (isLocal()) {

	$action = grabVar("action");
	switch($action) {
		case "mkfile":
			$file = grabVar("file");
			$file = sanitizeFileName($file);
			file_put_contents($file, "");
			print $file;
			break;
		case "mkdir":
			$folder = grabVar("folder");
			$folder = preg_replace("/\/\/+/","/", $folder);
			print "Make folder: " . $folder;
			mkdir($folder);
			break;
		case "save":
			$file = grabVar("file");
			$file = preg_replace("/\/\/+/","/", $file);
			if (realpath($file)==realpath("./editor.php")) {} else {
				$code = grabVar("code");
				$code = str_replace("&#39;", "'", $code);
				print $code;
				file_put_contents($file, $code);
			}
			break;
		case "delete":
			$file = grabVar("file");
			print "DELETE " . $file;
			unlink($file);
			break;
		case "deleteFolder":
			$folder = grabVar("folder");
			print "DELETE " . $folder;
			rmdirRF($folder);
			break;
		default:
			break;
	}


}
?>