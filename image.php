<?php 
$current = (isset($_GET['current']) ? "?current=".$_GET['current'] : "");
header("Location: index.php".$current);
?>