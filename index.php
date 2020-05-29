<?php

include_once 'lib/Config.php';
include_once 'lib/FolderUtil.php';
require_once ("config.php");

use swa\digitalphotoframe\lib\Config;
use swa\digitalphotoframe\lib\FolderUtil;

$current = isset($_GET['current']) && is_numeric($_GET['current']) ? $_GET['current'] : -1;
$files = FolderUtil::listFiles(Config::read("mail_image_directory_current"));

$next = $current;
if($current == -1) {
	$next = rand(0, count($files) -1);
} else if($next >= 0) {
	$next++; // next image
	if(count($files) <= $next) {
		$next = 0;
	}
} else {
	$next = 0;
}

$theImage = $files[$next];

setlocale(LC_TIME, Config::read("image_locale"));

// Handle Subject
$theMessage = "";
if(file_exists(Config::read("image_subject_file"))) {
	$file = fopen(Config::read("image_subject_file"), "r");
	$knownSenders = Config::read("image_known_senders");
	
	while(!feof($file)) {
		$currentLine = fgets($file);
		$currentLine = explode(";", $currentLine);
		$messageCreateDate = strtotime($currentLine[0]);
		if(strtotime("-".Config::read("image_message_age")." Days") < $messageCreateDate) {
			$date = strftime("%A, %d.%m", $messageCreateDate);
			$from = $currentLine[1];
			if(isset($knownSenders[$from])) {
				$from = $knownSenders[$from];
			}
			$msg = $currentLine[2];
			if($msg != "") {
				$theMessage .= "Nachricht von ".$from." am ".$date.": ".$msg." - - - - -  ";
			}
		} else {
			// this is an old message that will not be displayed
		}
	}
	fclose($file);
}

$nextURL = Config::read("image_url") . "?current=" . $next

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="<?php echo Config::read("image_refresh")?>; url=<?php echo $nextURL;?>" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<title><?php echo Config::read("gallery_name")?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
<style>
html,body{
    margin:0;
    height:100%;
}

body {background-color: black;}
#fixed-div {    position: fixed;    top: 1em;    right: 1em; }

@keyframes push {
    from {
        margin: 100%;
    }
    to {
        margin: 0;
    }
}
.marquee {
    white-space: nowrap;
    overflow: hidden;
	background-color: black;
	color: white;
	font-size: 1.4em;
}
.marquee > span:nth-child(1) {
    animation:  20s linear 0s infinite push;
}

img{
  display:block;
  width:100%; height:100%;
  object-fit: scale-down;
}

h1.error {
	color:red;
}
</style>

</head>

<body>

<?php if($theMessage != "") { ?>
<div class="marquee">
	<span><?php echo $theMessage; ?></span>
</div>
<?php } ?>

<?php
if ($theImage != "") {
	echo "<a href=\"".$nextURL."\"><img src=\"".$theImage."\" /></a>";
} else {
	echo "<h1 class=\"error\">No Picture found in ".Config::read("mail_image_directory_current")."</h1>";
}
?>

<!-- DEBUG
<?php print_r($files);?>
-->
</body>

</html>