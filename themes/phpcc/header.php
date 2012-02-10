<html>
<head>

<?php
if (!$site_title) {
  print "<title>phpCodeCabinetSI v".$version."</title>\n";
} else {
  print "<title>".$site_title."</title>\n";
}
if (!$allow_index) { print "<META NAME=\"ROBOTS\" CONTENT=\"NOINDEX,NOFOLLOW\">\n"; }
?>

  <link rel="stylesheet" href="<?php echo "$base_url/themes/$theme/"; ?>style.css" type="text/css">
  <link rel="stylesheet" title="Zenburn" href="include/highlight/styles/default.css">
  <link rel="alternate stylesheet" title="Zenburn" href="include/highlight/styles/vs.css">

<script src="include/highlight/highlight.pack.js"></script>
<script>
  hljs.tabReplace = '    ';0
  hljs.initHighlightingOnLoad();
</script>

</head>
<body>

<?php

if ($_GET['printable'] != 1) {

  echo '<table cellpadding="3" cellspacing="2" border="1" width="100%">
          <tr>
            <td width="85%" class="titletop" valign="top" align="left">

              <a class="mainmenu" href="index.php">MAIN MENU</a> |
       ';

    if ($_SESSION['isloggedin'] == $glbl_hash) {
      echo '<a class="mainmenu" href="user.php?op=logout">LOGOUT ('.$_SESSION['username'].')</a> | ';
    } else {
      echo '<a class="mainmenu" href="user.php">LOGIN</a> | ';
    }

    if ($_SESSION['isloggedin'] == $glbl_hash) {
      echo '<a class="mainmenu" href="user-config.php">USER INFO</a> | ';
    }

    if ($_SESSION['isadmin'] == $glbl_hash) {
      echo '<a class="mainmenu" href="admin.php">ADMINISTRATION</a> | ';
    }

  echo '
    <a class="mainmenu" href="browse.php">BROWSE</a> |
    <a class="mainmenu" href="search.php">SEARCH</a> |
       ';


  if (($_GET['cid']) && ($_SESSION['isloggedin'] == $glbl_hash)) {
      echo '<a class="mainmenu" href="input.php?cid='.strip_tags($_GET['cid']).'">ENTER CODE (IN THIS CATEGORY)</a>';
  } else {
      echo '<a class="mainmenu" href="input.php">ENTER CODE</a>';
  }


  if ($_SESSION['isloggedin'] == $glbl_hash) {
      echo ' | <a class="mainmenu" href="import.php">IMPORT</a>';
  }


  if (($_SESSION['isloggedin'] == $glbl_hash) || ($allow_anon_export == 1)) {
      echo ' | <a class="mainmenu" href="export.php">EXPORT</a>';
  }


  echo '
    </td>
    <td width="15%" class="titletop" valign="top" align="center">phpCodeCabinet v'.$version.'</td>
  </tr>
  <tr>
    <td valign="top" colspan="2">
       ';

} //if not print
