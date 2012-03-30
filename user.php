<?php

include_once("header.php");

if ($_GET['op'] == "logout") {

// if register_globals is turned on
session_unregister("isloggedin");
session_unregister("username");
session_unregister("userid");
session_unregister("isadmin");
session_unregister("user_theme");
session_unregister("user_email");
session_unregister("fullname");
session_unregister("export_snippets");
session_unregister("export_categories");

// if register_globals is turned off
unset($_SESSION['isloggedin']);
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['isadmin']);
unset($_SESSION['user_theme']);
unset($_SESSION['user_email']);
unset($_SESSION['fullname']);
unset($_SESSION['export_snippets']);
unset($_SESSION['export_categories']);

print '<p>&nbsp;</p><center>Logging out...</center><p>&nbsp;</p>';
print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=index.php">';

}


// Log user in if post variables are defined from form

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

if (($_POST['logmein']) && ($_POST['username']) && ($_POST['password'])) {

  //Make sure nobody tries to be shifty
  unset($_SESSION['isadmin']);
  $result1=db_query("SELECT userid,password,fullname,email,theme,admin FROM ".$prefix."users WHERE username='".$_POST['username']."'");
  $num_rows1 = db_num_rows($result1);
  list($userid,$pwd,$fullname,$user_email,$user_theme,$isadmin)=db_fetch_array($result1);
  if (($num_rows1 > 0) && (md5($_POST['password']) == $pwd)) {
    $_SESSION['userid'] = $userid;
    $_SESSION['isloggedin'] = $glbl_hash;
    $_SESSION['user_theme'] = $user_theme;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['user_email'] = $user_email;
    $_SESSION['username'] = $_POST['username'];
      if ($isadmin == 1) {
        $isadmin = $glbl_hash;
        $_SESSION['isadmin'] = $isadmin;
      }
  }

}


if (($_SESSION['isloggedin'] != $glbl_hash) && (!$_GET['op'])) {

echo '
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <form action="'.$_SERVER['PHP_SELF'].'" method="POST">
    <table align="center" width="250" border="1">
      <tr>
        <td class="indextitle" align="center" valign="top">
        <font size="4">Acceso:</font><br><br>
        <font size="2">USERNAME: (Case-sensitive)</font>
        <input type="text" name="username" size="35"><br>
        <font size="2">PASSWORD: (Case-sensitive)</font>
        <input type="password" name="password" size="35"><br><br>
        <input type="hidden" name="logmein" value="1">
        <center><input type="Submit" name="Submit" value="LOGIN"></center>
        <br>
        </td>
      </tr>
    </table>
    </form>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
     ';

} else if ($_SESSION['isloggedin'] == $glbl_hash) {
  print '<p>&nbsp;</p><center>One moment...</center><p>&nbsp;</p>';
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=index.php">';
}

include_once("footer.php");
?>
