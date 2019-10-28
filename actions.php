<?php
ini_set("display_errors", 1);
include("fun.php");
header("Content-type:text/plain");

if (isLocal()) {

	$action = grabVar("action");
	switch($action) {
		case "mkfile":
			$file = grabVar("file");
			$file = sanitizeFileName($file);
			$okpath = checkDirPath($file);
			if ($okpath==$file) {
				file_put_contents($file, "");
//				print $file;
			}
			break;
		case "mkdir":
			$folder = grabVar("folder");
			$folder = preg_replace("/\/\/+/","/", $folder);
			print "Make folder: " . $folder;
			$okpath = checkDirPath($folder);
			if ($okpath==$folder) {
				mkdir($folder);
			}
			break;
		case "save":
			$file = grabVar("file");
			$file = preg_replace("/\/\/+/","/", $file);
			$okpath = checkDirPath($file);
			if ($okpath==$file) {
				if (realpath($file)==realpath("./editor.php")) {} else {
					$code = grabVar("code");
					$code = str_replace("&#39;", "'", $code);
					print $code;
					file_put_contents($file, $code);
				}
			}
			break;
		case "delete":
			$file = grabVar("file");
			$okpath = checkDirPath($file);
			if ($okpath==$file) {
				print "DELETE " . $file;
				unlink($file);
			}
			break;
		case "deleteFolder":
			$folder = grabVar("folder");
			$okpath = checkDirPath($folder);
			if ($okpath==$folder) {
				print "DELETE " . $folder;
				rmdirRF($folder);
			}
			break;
		default:
			break;
	}


}
?>