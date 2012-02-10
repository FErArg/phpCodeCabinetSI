<?php
// Intended to serve as database abstraction layer for PostgreSQL
// Limited to functions required by phpCodeCabinet
// Global variables from config.php

function db_connect($dbname) {
	global $dbhost,$dbuser,$dbpass;
        $connect_string = "host=" . $dbhost . " dbname=" . $dbname . " user=" . $dbuser . " password= " . $dbpass;
        //echo $connect_string;
        $connect = pg_connect($connect_string);
	if (!$connect) {
		echo pg_errormessage();
	}
	return $connect;
}

function db_query($qstring) {
	global $dbname;
        //echo $qstring;
	$query = pg_exec($qstring);
        //echo $query;
    if (!$query) {
         $query = pg_errormessage();
    }
    return $query;
}

function db_num_rows($qhandle) {
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return @pg_numrows($qhandle);
	} else {
		return 0;
	}
}

function db_fetch_array($qhandle) {
    return @pg_fetch_array($qhandle);
}
?>
