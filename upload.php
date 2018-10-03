<?php
ini_set("display_errors", 1);
include("php/fun.php");
if (isLocal()) {
	
	$path = grabVar("path");
	$action = grabVar("action");
	
//	print $path . "<br />";
//	print $action . "<Br />";
	
	if ($path!="" && $action=="uploadFile") {
		$path = $path . "/";
		$path = preg_replace("/\/\/+/","/", $path);
		$target_file = $path . basename($_FILES["fileToUpload"]["name"]);
//		print $target_file;
		move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
		?>
		<script type='text/javascript'>
		setTimeout("parent.location.reload();", 100);
		</script>
		<?php
	} else {
	
	
?>

<style>
html, body, p, div {
	margin:0; padding:0;
}
</style>

<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
	<input type="hidden" name="path" value="<?php print $path; ?>" />
	<input type="hidden" name="action" value="uploadFile" />
    <input type="submit" value="Upload File" name="submit" />
</form>

<?php
	}
}
?>