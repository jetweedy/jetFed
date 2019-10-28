<?php

$requested = str_replace("/jetFed/get/","",$_SERVER['REQUEST_URI']);
$requested = str_replace("..","",$requested);
if (file_exists("files/".$requested)) {
	print file_get_contents("files/".$requested);	
}

?>