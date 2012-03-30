<?php

# Configuration file for phpCodeCabinet


/* URL for phpCC */
# NOTE: No trailing slash
$base_url = "http://serinformaticos.mine.nu:6088/estudios/SerInformaticos/phpCodeCabinetSI/phpCodeCabinetSI";
// $base_url = "http://serinformaticos.mine.nu:6088/phpcc";


/* Database Abstraction Layer Directory */
# Note: Location is relative to $base_url
$daldir = "dal";


/* Database table prefix */
$prefix = "phpcc_";


/* Database Connection Info */
$dbtype = "mysql";      // Database type
                        // mysql = MySQL
                        // pgsql = PostgreSQL

$dbhost = "localhost";  // Database hostname
$dbname = "phpCodeCabinet";           // Database name
$dbuser = "phpCodeCabinet";           // Database username
$dbpass = "156379FErArg";           // Database password


# Snippet comment options
$allow_comments = 1;	// 0 = No Comments
			// 1 = Only authenticated users & admins
			// 2 = Anyone can post comments


/* Themes */
# The directory name of the theme you wish to use.
$theme = "phpcc";


/* HFile Directory */
# The "HFile" directory is used by the Beautifier
# syntax highlighter.  You will need to provide the
# full path to your HFile directory.
#$HFile_dir = "/var/www/phpcc/include/HFile";


/* Site Title */
# Simple HTML <title>, used primarily for
# standalone installations.  Do not include html tags.
$site_title = "Cat&aacute;logo de Scripts";


/* Allow Indexing */
# Prevent indexing and following of links for
# spidering search engines.
$allow_index = 0; // (0 = No)


/* Allowed HTML Tags */
# Select which HTML tags should be allowed in snippet
# entry fields for (description, comments, etc.).
# NOTE: All HTML tags are allowed in the snippet field.
$allowed_html_tags = "<b><i>"; // <a><b><i><u>


/* Permission to Export */
# Allow anonymous users to export categories and/or
# snippets.  If disabled, only logged in users have
# this privilege.  Note that this does not affect
# imports - users must still be logged in to import.
$allow_anon_export = 0;  // (0 = No)


/* Global Hash */
# The global hash string is used to ensure that your
# site remains secure.  Be sure to change this from
# the default to something unique.
$glbl_hash = "*&fp7uC-9DA#2^jF&}E1|K8Q%dP/T7bo7uf{1]}|a:Pa_pm4O.";




#############################
# DO NOT EDIT BELOW THIS LINE
#############################

/* Version */
$version = "0.6.1";

switch (strtolower($dbtype)) {
    case ("mysql"):
      require_once("$daldir/mysql.inc");
      break;
    case ("pgsql"):
      require_once("$daldir/pgsql.inc");
      break;
    /* //feel free to implement other dbs here
    case ("something-else"):
      require_once("$daldir/something-else.inc");
      break;
    */
}

db_connect($dbname);

GLOBAL $base_url,$version,$theme,$site_title,$allow_index,$glbl_hash,$allow_comments,$allowed_html_tags,$allow_anon_export;

?>
