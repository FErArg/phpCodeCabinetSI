<?php

include_once("header.php");


if (!$_GET['install']) {
  echo "<p>&nbsp;</p>
        <p>&nbsp;</p>

        <center>
        Select one of the following installation options:<br><br>
        <a href='setup.php?install=check'>Check Configuration</a><br>
        <a href='setup.php?install=new'>New Install</a><br>
        <a href='setup.php?install=upgrade'>Upgrade</a><br>
        </center>

        <p>&nbsp;</p>
        <p>&nbsp;</p>";
}

if (($_GET['install'] == "upgrade") && (!$_GET['ver'])) {
  echo "<p>&nbsp;</p>
        <p>&nbsp;</p>

        <center>
        From which version are you upgrading:<br><br>
        <a href='setup.php?install=upgrade&ver=0.1'>0.1</a><br>
        <a href='setup.php?install=upgrade&ver=0.2'>0.2</a><br>
        <a href='setup.php?install=upgrade&ver=0.3'>0.3</a><br>
        <a href='setup.php?install=upgrade&ver=0.4'>0.4</a><br>
        </center>

        <p>&nbsp;</p>
        <p>&nbsp;</p>";
}


$status .= "<p>&nbsp;</p><table border='1' width='600' align='center' cellspacing='2' cellpadding='3'>";


if ($_GET['install'] == "check") {

    unset($err_check);

    if ($base_url == "http://mydomain.com/phpcc") { $err_check .= '$base_url NOT DEFINED<br>'; }
    if (!is_dir($daldir)) { $err_check .= '$daldir NOT VALID DIRECTORY<br>'; }
    if (!$prefix) { $err_check .= '$prefix NOT DEFINED<br>'; }
    if (!$dbtype) { $err_check .= '$dbtype NOT DEFINED<br>'; }
    if (!$dbhost) { $err_check .= '$dbhost NOT DEFINED<br>'; }
    if (!$dbname) { $err_check .= '$dbname NOT DEFINED<br>'; }
    if (!$dbuser) { $err_check .= '$dbuser NOT DEFINED<br>'; }
    if (!$dbpass) { $err_check .= '$dbpass NOT DEFINED<br>'; }
    if (!$theme) { $err_check .= '$theme NOT DEFINED<br>'; }
    if (!is_dir($HFile_dir)) { $err_check .= '$HFile_dir NOT VALID DIRECTORY<br>'; }
    if ($glbl_hash == "32kkdskeidkseYc") { $err_check .= '$glbl_hash SHOULD BE CHANGED<br>'; }

    if (!$err_check) {
       $status .= "<tr><td align='left' bgcolor='#00FF00'><b>Configuration Valid!</b></td></tr>";
    } else {
       $status .= "<tr><td align='left' bgcolor='#FF0000'><b>Error(s) in Configuration Options:<br><br><i>$err_check</i><br></b></td></tr>";
    }

    $finished = 1;

}



if ($_GET['install'] == "new") {

  unset($err_install,$query1,$query2,$query3,$query3a);

  if ($dbtype == "mysql") {

      $build_query = "CREATE TABLE ".$prefix."categories (
                        cid int(11) NOT NULL auto_increment,
                        category_name text NOT NULL,
                        parent_id int(11) NOT NULL default '0',
                        description text NOT NULL,
                        owner_id int(11) NOT NULL default '0',
                        PRIMARY KEY  (cid),
                        KEY parent_id (parent_id)
                      )";

  } else if ($dbtype == "pgsql") {

      $build_query = "CREATE TABLE ".$prefix."categories (
                        cid serial NOT NULL,
                        category_name text NOT NULL,
                        parent_id integer NOT NULL default '0',
                        description text NOT NULL,
                        owner_id integer NOT NULL default '0',
                        PRIMARY KEY  (cid)
                      );";

      $build_query .= "CREATE INDEX parent_id_".$prefix."categories_key ON ".$prefix."categories(parent_id);";

  }

  $query1 = db_query($build_query);
  unset($build_query);

  if (preg_match("/exists/i", $query1)) {
    $err_install .= $query1."<br>";
  }


  if ($dbtype == "mysql") {

      $build_query = "CREATE TABLE ".$prefix."snippets (
                        sid int(11) NOT NULL auto_increment,
                        name text NOT NULL,
                        description text NOT NULL,
                        comment text NOT NULL,
                        author_name text NOT NULL,
                        author_email text NOT NULL,
                        language text NOT NULL,
                        highlight_mode text NOT NULL,
                        category_id int(11) NOT NULL default '0',
                        last_modified datetime NOT NULL default '0000-00-00 00:00:00',
                        owner_id int(11) NOT NULL default '0',
                        snippet longtext NOT NULL,
                        PRIMARY KEY  (sid),
                        KEY owner_id (owner_id),
                        KEY category_id (category_id)
                      )";

  } else if ($dbtype == "pgsql") {

      $build_query = "CREATE TABLE ".$prefix."snippets (
                        sid serial NOT NULL,
                        name text NOT NULL,
                        description text NOT NULL,
                        comment text NOT NULL,
                        author_name text NOT NULL,
                        author_email text NOT NULL,
                        language text NOT NULL,
                        highlight_mode text NOT NULL,
                        category_id integer NOT NULL default '0',
                        last_modified timestamp NOT NULL default '1900-01-01 00:00:00',
                        owner_id integer NOT NULL default '0',
                        snippet text NOT NULL,
                        PRIMARY KEY  (sid)
                      );";

      $build_query .= "CREATE INDEX category_id_".$prefix."snippets_key ON ".$prefix."snippets(category_id);";
      $build_query .= "CREATE INDEX owner_id_".$prefix."snippets_key ON ".$prefix."snippets(owner_id);";

  }


  $query2 = db_query($build_query);
  unset($build_query);

  if (preg_match("/exists/i", $query2)) {
    $err_install .= $query2."<br>";
  }


  if ($dbtype == "mysql") {

      $build_query = "CREATE TABLE ".$prefix."users (
                        userid int(11) NOT NULL auto_increment,
                        username varchar(255) NOT NULL default '',
                        password text NOT NULL,
                        fullname text NOT NULL,
                        email text NOT NULL,
                        theme text NOT NULL,
                        admin int(1) NOT NULL default '0',
                        PRIMARY KEY  (userid),
                        KEY username (username)
                      )";

  } else if ($dbtype == "pgsql") {

      $build_query = "CREATE TABLE ".$prefix."users (
                        userid serial NOT NULL,
                        username varchar(255) NOT NULL default '',
                        password text NOT NULL,
                        fullname text NOT NULL,
                        email text NOT NULL,
                        theme text NOT NULL,
                        admin integer NOT NULL default '0',
                        PRIMARY KEY  (userid)
                      );";
	
      $build_query .= "CREATE INDEX username_".$prefix."users_key ON ".$prefix."users(username);";

  }

  $query3 = db_query($build_query);
  unset($build_query);

  if (preg_match("/exists/i", $query3)) {
    $err_install .= $query3."<br>";
  }

  if (!$err_install) {
      $query3a = db_query("INSERT INTO ".$prefix."users (username, password, fullname, email, theme, admin) VALUES ('admin', 'cae1ec0a768139004227f1f110816a15', '','', 'phpcc', 1)");
  }
  
  
  if ($dbtype == "mysql") {

      $build_query = "CREATE TABLE ".$prefix."user_comments (
  		        comment_id int(11) NOT NULL auto_increment,
		        snippet_id int(11) NOT NULL default '0',
		        subject text NOT NULL,
		        comment longtext NOT NULL,
		        last_modified datetime NOT NULL default '0000-00-00 00:00:00',
		        owner_name text NOT NULL,
		        owner_email text NOT NULL,
	 	        owner_id int(11) NOT NULL default '0',
		        PRIMARY KEY  (comment_id),
		        KEY comment_id (comment_id),
		        KEY snippet_id (snippet_id),
		        KEY owner_id (owner_id)
		      )";

  } else if ($dbtype == "pgsql") {

      $build_query = "CREATE TABLE ".$prefix."user_comments (
                        comment_id serial NOT NULL,
			snippet_id integer NOT NULL default '0',
		        subject text NOT NULL,
		        comment text NOT NULL,
			last_modified timestamp NOT NULL default '1900-01-01 00:00:00',
		        owner_name text NOT NULL,
		        owner_email text NOT NULL,
			owner_id integer NOT NULL default '0',
		        PRIMARY KEY  (comment_id)
		      );";

      $build_query .= "CREATE INDEX comment_id_".$prefix."user_comments_key ON ".$prefix."user_comments(comment_id);";
      $build_query .= "CREATE INDEX snippet_id_".$prefix."user_comments_key ON ".$prefix."user_comments(snippet_id);";
      $build_query .= "CREATE INDEX owner_id_".$prefix."user_comments_key ON ".$prefix."user_comments(owner_id);";

  }

  $query4 = db_query($build_query);
  unset($build_query);


  if (preg_match("/exists/i", $query4)) {
    $err_install .= $query4."<br>";
  }



  if (!$err_install) {
    $status .= "<tr><td align='left' bgcolor='#00FF00'><b>Initial database table construction complete.</b></td></tr>";
  } else {
    $status .= "<tr><td align='left' bgcolor='#FF0000'><b>There were errors duing the initial database setup:<br><br><i>$err_install</i></b><br></td></tr>";
  }

  $finished = 1;

} // End (Initial Install)



// Upgrade to v0.2
if ((($_GET['install'] == "new") && ($dbtype == "mysql") && (!$err_install)) || (($_GET['install'] == "upgrade") && ($_GET['ver']) && ($_GET['ver'] < 0.2))) {

  $check1 = db_query("SELECT * FROM ".$dbname.".categories LIMIT 1");
  if ($check1 != "Table '".$dbname.".categories' doesn't exist") {
    $query1 = db_query("ALTER TABLE ".$dbname.".categories RENAME TO ".$dbname.".".$prefix."categories");
    if ($query1 != 1) { $err_upgrade .= $query1."<br>"; }
  } else if ($_GET['install'] == "upgrade") {
    $err_upgrade .= $check1."<br>";
  }
  $check2 = db_query("SELECT * FROM ".$dbname.".snippets LIMIT 1");
  if ($check2 != "Table '".$dbname.".snippets' doesn't exist") {
    $query2 = db_query("ALTER TABLE ".$dbname.".snippets RENAME TO ".$dbname.".".$prefix."snippets");
    if ($query2 != 1) { $err_upgrade .= $query2."<br>"; }
  } else if ($_GET['install'] == "upgrade") {
    $err_upgrade .= $check2."<br>";
  }
  $check3 = db_query("SELECT * FROM ".$dbname.".users LIMIT 1");
  if ($check3 != "Table '".$dbname.".users' doesn't exist") {
    $query3 = db_query("ALTER TABLE ".$dbname.".users RENAME TO ".$dbname.".".$prefix."users");
    if ($query3 != 1) { $err_upgrade .= $query3."<br>"; }
  } else if ($_GET['install'] == "upgrade"){
    $err_upgrade .= $check3."<br>";
  }

  $sanity_check = db_query("SELECT * FROM ".$dbname.".".$prefix."users");
  if ($sanity_check == "Table '".$dbname.".".$prefix."users' doesn't exist") {
    $err_upgrade .= "Necessary database tables do not exist.  Have you already performed an initial install?";
  }

  if (!$err_upgrade) {
    $status .= "<tr><td align='left' bgcolor='#00FF00'><b>v0.2 modifications complete.</b></td></tr>";
  } else {
    $status .= "<tr><td align='left' bgcolor='#FF0000'><b>There were errors in v0.2 modifications:<br><br><i>$err_upgrade</i></b><br></td></tr>";
  }

  $finished = 1;

} else if ($dbtype == "pgsql") {

  // There was no v0.1 for PostgreSQL, hence there is no reason to run this portion of the upgrade.

}



// Upgrade to v0.3
if (($_GET['install'] == "upgrade") && ($_GET['ver']) && ($_GET['ver'] < 0.3)) {

  // No changes were made to the database structure in v0.3.
  // In order to maintain consistency, we'll just add an upgrade option
  // that doesn't actually do anything.
  $finished = 1;

}



// Upgrade to v0.4
if (($_GET['install'] == "upgrade") && ($_GET['ver']) && ($_GET['ver'] < 0.4)) {

  if ($dbtype == "mysql") {

    $query4a = db_query("ALTER TABLE ".$prefix."categories ADD INDEX (parent_id)");
    if ($query4a != 1) { $err_upgrade .= $query4a."<br>"; }
    $query4b = db_query("ALTER TABLE ".$prefix."snippets ADD INDEX (owner_id)");
    if ($query4b != 1) { $err_upgrade .= $query4b."<br>"; }
    $query4c = db_query("ALTER TABLE ".$prefix."snippets ADD INDEX (category_id)");
    if ($query4c != 1) { $err_upgrade .= $query4c."<br>"; }
    $query4d = db_query("ALTER TABLE ".$prefix."users ADD INDEX (username)");
    if ($query4d != 1) { $err_upgrade .= $query4d."<br>"; }
    $query4e = db_query("CREATE TABLE ".$prefix."user_comments (
  		           comment_id int(11) NOT NULL auto_increment,
		           snippet_id int(11) NOT NULL default '0',
		           subject text NOT NULL,
		           comment longtext NOT NULL,
		           last_modified datetime NOT NULL default '0000-00-00 00:00:00',
		           owner_name text NOT NULL,
		           owner_email text NOT NULL,
	 	           owner_id int(11) NOT NULL default '0',
		           PRIMARY KEY  (comment_id),
		           KEY comment_id (comment_id),
		           KEY snippet_id (snippet_id),
		           KEY owner_id (owner_id)
		         )");
    if ($query4e != 1) { $err_upgrade .= $query4e."<br>"; }


  } else if ($dbtype == "pgsql") {

    $query4a = db_query("CREATE INDEX parent_id_".$prefix."categories_key ON ".$prefix."categories(parent_id)");
    if (preg_match("/exists/i", $query4a)) { $err_upgrade .= $query4a."<br>"; }
    $query4b = db_query("CREATE INDEX category_id_".$prefix."snippets_key ON ".$prefix."snippets(category_id)");
    if (preg_match("/exists/i", $query4b)) { $err_upgrade .= $query4b."<br>"; }
    $query4c = db_query("CREATE INDEX owner_id_".$prefix."snippets_key ON ".$prefix."snippets(owner_id)");
    if (preg_match("/exists/i", $query4c)) { $err_upgrade .= $query4c."<br>"; }
    $query4d = db_query("CREATE INDEX username_".$prefix."users_key ON ".$prefix."users(username)");
    if (preg_match("/exists/i", $query4d)) { $err_upgrade .= $query4d."<br>"; }
    $query4e = db_query("CREATE TABLE ".$prefix."user_comments (
                           comment_id serial NOT NULL,
			   snippet_id integer NOT NULL default '0',
		           subject text NOT NULL,
		           comment text NOT NULL,
			   last_modified timestamp NOT NULL default '1900-01-01 00:00:00',
		           owner_name text NOT NULL,
		           owner_email text NOT NULL,
			   owner_id integer NOT NULL default '0',
		           PRIMARY KEY  (comment_id)
		         )");
    if (preg_match("/exists/i", $query4e)) { $err_upgrade .= $query4e."<br>"; }
    $query4f = db_query("CREATE INDEX comment_id_".$prefix."user_comments_key ON ".$prefix."user_comments(comment_id)");
    if (preg_match("/exists/i", $query4f)) { $err_upgrade .= $query4f."<br>"; }
    $query4g = db_query("CREATE INDEX snippet_id_".$prefix."user_comments_key ON ".$prefix."user_comments(snippet_id)");
    if (preg_match("/exists/i", $query4g)) { $err_upgrade .= $query4g."<br>"; }
    $query4h = db_query("CREATE INDEX owner_id_".$prefix."user_comments_key ON ".$prefix."user_comments(owner_id)");
    if (preg_match("/exists/i", $query4h)) { $err_upgrade .= $query4h."<br>"; }

  }


  if (!$err_upgrade) {
    $status .= "<tr><td align='left' bgcolor='#00FF00'><b>v0.4 modifications complete.</b></td></tr>";
  } else {
    $status .= "<tr><td align='left' bgcolor='#FF0000'><b>There were errors in v0.4 modifications:<br><br><i>$err_upgrade</i></b><br></td></tr>";
  }

  $finished = 1;

}



// Upgrade to v0.5
if (($_GET['install'] == "upgrade") && ($_GET['ver']) && ($_GET['ver'] < 0.5)) {

  // No changes were made to the database structure in v0.5.
  // In order to maintain consistency, we'll just add an upgrade option
  // that doesn't actually do anything.
  $finished = 1;

}



if (($_GET['install'] == "new") || ($_GET['install'] == "upgrade")) {

    if (($finished == 1) && (!$err_install) && (!$err_upgrade)) {
        $status .= "<tr><td align='left' bgcolor='#00FF00'><b>Setup Complete!  Remember to delete your 'setup.php' file.</b></td></tr>";
        $status .= "<tr><td align='left' bgcolor='#00FF00'><b>You will need to login <a href=\"user.php\">here</a> to proceed.<br>
                    DEFAULT USERNAME: admin  |  DEFAULT PASSWORD: phpcc</b></td></tr>";
    } else {
        $status .= "</tr><td align='left' bgcolor='#FF0000'><b>There were errors, and setup did not finish properly.</b></td></tr>";
    }

}

$status .= "</table><p>&nbsp;</p>";

if (($finished == 1) && (($err_install) || ($err_upgrade))) {
  $status .= "<center><a href='setup.php'>Try Again</a><center><p>&nbsp;</p>";
} else if (($finished == 1) && ($_GET['install'] == "check")) {
  $status .= "<center><a href='setup.php?install=check'>Try Again</a> | <a href='setup.php'>Back to Setup</a><center><p>&nbsp;</p>";
}


if ($finished == 1) { echo $status; }

include_once("footer.php");

?>
