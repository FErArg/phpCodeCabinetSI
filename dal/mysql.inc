<?php
// Intended to serve as database abstraction layer for MySQL
// Limited to functions required by phpCodeCabinet
// Global variables from config.php

function db_connect($dbname) {
	global $dbhost,$dbuser,$dbpass;
	$connect = mysql_connect($dbhost,$dbuser,$dbpass) or die(mysql_error());
        $db = mysql_select_db($dbname, $connect);
	if (!$db) {
		$db = mysql_error();
	}
	return $db;
}

function db_query($qstring) {
	global $dbname;
	$query = mysql_query($qstring);
    if (!$query) {
        $query = mysql_error();
    }
    return $query;
}

function db_num_rows($qhandle) {
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return @mysql_num_rows($qhandle);
	} else {
		return 0;
	}
}

function db_fetch_array($qhandle) {
	return @mysql_fetch_array($qhandle);
}

?>
