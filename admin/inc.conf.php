<?php
session_start();
$lang = isset($_GET["lang"]) ? $_GET["lang"] : (isset($_SESSION["lang"]) ? $_SESSION["lang"] : "en_US" ); $_SESSION["lang"] = $lang;
?>