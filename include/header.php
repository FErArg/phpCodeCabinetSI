<?php
session_start();
require_once("config.php");

if ($_SESSION['isloggedin'] == $glbl_hash) { $theme = $_SESSION['user_theme']; }

include_once("themes/$theme/header.php");

?>
<img src="http://www.serinformaticos.es/piwik/piwik.php?idsite=9&rec=1" style="border:0" alt="" />
