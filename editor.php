<?php

//// --------------------------------------------------------------
//// Config
//// --------------------------------------------------------------
$WEBROOTPATH = "/var/www/html";
$WEBDOMAIN = "http://localhost:8187";
//// --------------------------------------------------------------

ini_set("display_errors", 1);
include("php/fun.php");
if (isLocal()) {

	$modes = array(
		"js" => "javascript"
		, "php" => "php"
		, "py" => "python"
		, "css" => "css"
		, "html" => "html"
		, "xml" => "xml"
	);

	$code = "";
	$path = realpath(grabVar("path"));
	$file = grabVar("file");
	$ext = "";
	if ($file!="") {
		$fa = explode(".",$file);
		$fe = end($fa);
		$ext = strtolower($fe);
	}
	if ($file!="" && file_exists($path."/".$file)) {
		$code = file_get_contents($path."/".$file);
	}
	if ($path=="") {
		$path = getcwd();
	}
//	print "<strong>Path:</strong> " . $path . "/" . $file;
//	print " | " . $ext;
//	print "<hr />";
	$sd = scandir($path);
	$files = array();
	$directories = array();
	foreach($sd as $f) {
//		if ($f[0]!=".") {
			if (is_dir($path."/".$f)) {
				$directories[] = $f;
			} else {
				$files[] = $f;
			}
//		}
	}
	
$urlpath = str_replace($WEBROOTPATH,"",$path);
if (substr($path, 0, strlen($WEBROOTPATH)) == $WEBROOTPATH) {
    $urlpath = substr($path, strlen($WEBROOTPATH));
	if ($urlpath=="") { $urlpath = "/"; }
} else {
	$urlpath = "";
}
if ( strlen($urlpath)>0 && $urlpath[strlen($urlpath)-1]!="/" ) {
	$urlpath .= "/";
}
//print $urlpath . "<hr />";

	
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Editor</title>
  <link rel="stylesheet" type="text/css" href="editor.css" />
</head>
<body>

<button id="btnSave">Save</button>
<button id="btnCreateFolder">New Folder</button>
<button id="btnCreateFile">New File</button>
<iframe id="uploadframe" src="upload.php?path=<?php print $path; ?>"></iframe>


<div id="filetree">
<?php
foreach($files as $f) {
	print "<div class='filerow'>";
	print "&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"javascript:deleteFile('".$path."/".$file."');\">[X]</a>";
	if ($urlpath!="") {
		print " <a href='".$urlpath.$f."' target='_blank'>[&gt;]</a>";
	}
	print "&nbsp;&nbsp;&nbsp;&nbsp; <a href='?path=".$path."&file=".$f."'>".$f."</a>";
	print "</div>";
}
print "<br /><br />";
foreach($directories as $directory) {
	print "<div class='filerow'>";
	print "&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"javascript:deleteFolder('".$path."/".$directory."');\">[X]</a>";
	print "&nbsp;&nbsp;";
	print "<a href='?path=".$path."/".$directory."'>[+] ".$directory."</a>";
	print "</div>";
}
?>
</div>
<pre id="editor"><?php
	$code = str_replace("<","&lt;",$code);
	$code = str_replace(">","&gt;",$code);
	print $code;
 
 ?></pre>

<script type="text/javascript" charset="utf-8" src="main.js"></script>
<script type="text/javascript" charset="utf-8" src="ajax.js"></script>
<script src="ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
	function deleteFile(f) {
		if (confirm("Are you sure you want to delete '" + f + "'?")) {
			ajax("GET", "actions.php", "action=delete&file="+encodeURIComponent(f), function(x) {
//				console.log(x);
				window.location.reload();
			}, false);
		}
	}
	function deleteFolder(f) {
		if (confirm("Are you sure you want to delete '" + f + "'?")) {
			ajax("GET", "actions.php", "action=deleteFolder&folder="+encodeURIComponent(f), function(x) {
//				console.log(x);
				window.location.reload();
			}, false);
		}
	}
	var FILE = <?php 
		if ($file!="") {
			print "\"" . $path . "/" . $file . "\""; 
		} else {
			print "false";
		}
	?>;

	var btnCreateFile = document.getElementById('btnCreateFile');
	btnCreateFile.addEventListener("click", function() {
		var fname = prompt("File Name:");
		if (typeof name != "undefined" && name!=null && trim(fname)!="") {
			ajax("GET", "actions.php", "action=mkfile&file="+encodeURIComponent("<?php print $path; ?>/"+fname), function(x) {
//				console.log(x);
				window.location.reload();
			}, false);
		}
	});


	var btnCreateFolder = document.getElementById('btnCreateFolder');
	btnCreateFolder.addEventListener("click", function() {
		var fname = prompt("Folder Name:");
		if (typeof name != "undefined" && name!=null && trim(fname)!="") {
			ajax("GET", "actions.php", "action=mkdir&folder="+encodeURIComponent("<?php print $path; ?>/"+fname), function(x) {
//				console.log(x);
				window.location.reload();
			}, false);
		}
	});
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/tomorrow_night");
	<?php
	if (isset($modes[$ext])) { 
		print "editor.session.setMode(\"ace/mode/".$modes[$ext]."\");";
	}
	?>
	var mySaveFunction = function() {
		if (!!FILE) {
			//// EDIT save action here (like ajax or whatever):
			var code = encodeURIComponent(editor.getValue());
			params = "action=save&file="+FILE+"&code="+code;
//			console.log("params: ", params);
			ajax("POST", "actions.php", params, function(x) {
//				console.log(x);
			}, false);
		}
	}
	if (!FILE) {
		document.getElementById('btnSave').style.display = "none";
	}
	document.getElementById('btnSave').addEventListener("click", mySaveFunction);
	window.addEventListener("keydown", function(e) {
		var keycode = e.keyCode? e.keyCode : e.charCode;
		if (keycode+""=="83" && e.ctrlKey) {
			mySaveFunction();
			e.preventDefault();
			return false;
		}
	});
	
	
	
</script>

</body>
</html>


<?php
}
?>