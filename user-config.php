<?php

include_once("header.php");

function list_themes($dir) {
// Returns array of directory names from $dir

  $i=0;
  if ($use_dir = @opendir($dir)) {
    while (($file = readdir($use_dir)) !== false) {
          if ($file != "." && $file != "..") {
             $theme_arr[$i] = "$file";
             $i++;
          }
    }
    closedir($use_dir);
  }

return $theme_arr;
}

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

if (($_POST['changeyou'] == 1) && ($_SESSION['isloggedin'] == $glbl_hash)) {

  // Change theme
  if ($_POST['user_theme']) {
   $result_new_theme=db_query("UPDATE ".$prefix."users SET theme='".$_POST['user_theme']."' WHERE userid='".$_SESSION['userid']."'");
   unset($_SESSION['user_theme']);
   $_SESSION['user_theme'] = $_POST['user_theme'];
   $info .= "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=user-config.php\">";
  }

  if (($_POST['full_name']) || ($_POST['email'])) {
   $result_new_contact_info=db_query("UPDATE ".$prefix."users SET fullname='".$_POST['full_name']."', email='".$_POST['email']."' WHERE userid='".$_SESSION['userid']."'");
  }

  // Change username
  if (($_POST['new_username']) && ($_POST['curr_username'] != $_POST['new_username'])) {
    $result_new_username=db_query("UPDATE ".$prefix."users SET username='".$_POST['new_username']."', fullname='".$_POST['full_name']."', email='".$_POST['email']."' WHERE userid='".$_SESSION['userid']."'");
    unset($_SESSION['username']);
    $_SESSION['username'] = $_POST['new_username'];
    $info .= "<br><h3 align='center' class='info'>Username changed successfully.</h3>";
  }

  // Change password
  if (($_POST['curr_pass']) && ($_POST['new_pass']) && ($_POST['confirm_pass']) && ($_POST['new_pass'] != $_POST['curr_pass']) && ($_POST['new_pass'] == $_POST['confirm_pass'])) {
    $result_get_pw=db_query("SELECT password FROM ".$prefix."users WHERE userid='".$_SESSION['userid']."'");
    list($pwd)=db_fetch_array($result_get_pw);
    if (md5($_POST['curr_pass']) == $pwd) {
      $new_pass = md5($_POST['new_pass']);
      $result_new_password=db_query("UPDATE ".$prefix."users SET password='$new_pass', fullname='".$_POST['full_name']."', email='".$_POST['email']."' WHERE userid='".$_SESSION['userid']."'");
      $info .= "<br><h3 align='center' class='info'>Password changed successfully.</h3>";
    } else {
      $info .= "<br><h3 align='center' class='info'>Incorrect password.</h3>";
    }
  } elseif (($_POST['curr_pass']) && ($_POST['new_pass']) && ($_POST['confirm_pass']) && ($_POST['new_pass'] != $_POST['confirm_pass'])) {
    $info .= "<br><h3 align='center' class='info'>Sorry, your new password did not match what you entered in the 'Confirm' box.</h3>";
  } elseif (($_POST['curr_pass']) && ($_POST['new_pass']) && ($_POST['confirm_pass']) && ($_POST['curr_pass'] == $_POST['new_pass'])) {
    $info .= "<br><h3 align='center' class='info'>You need to enter a <i>different</i> password if you wish to change your password.</h3>";
  }

echo $info;

}

if ($_SESSION['isloggedin'] == $glbl_hash) {

$get_user_info = db_query("SELECT fullname,email,theme FROM ".$prefix."users WHERE userid='".$_SESSION['userid']."'");
list($fullname,$email,$user_theme) = db_fetch_array($get_user_info);

echo '<p>&nbsp;</p>
      <h3 align="center">User Configuration Options for <i>'.$_SESSION['username'].'</i>:</h3>
         <form name="changeyou" action="'.$_SERVER['PHP_SELF'].'" method="POST">
         <table align="center" width="600" border="1" cellspacing="0" cellpadding="3">
           <tr valign="top">
             <td class="userinfo" align="center"><h3>CHANGE USERNAME</h3><br>
             Current Username:<br><input type="text" name="curr_username" value='.$_SESSION['username'].' size="35"><br>
             Change Username:<br><input type="text" name="new_username" size="35"><br><br>

             </td>
             <td class="userinfo" align="center"><h3>CHANGE PASSWORD</h3><br>
             Current Password:<br><input type="password" name="curr_pass" size="35"><br>
             New Password:<br><input type="password" name="new_pass" size="35"><br>
             Confirm New Password:<br><input type="password" name="confirm_pass" size="35"><br><br>
             </td>
           </tr>
           <tr>
             <td class="userinfo" align="center"><h3>CHANGE THEME</h3><br>
               Preferred Theme: <select name="user_theme" size="1">';


           if ($user_theme) {
               echo '<option value="0" selected>'.$user_theme.'</option>';
           }

           $theme_arr = list_themes("themes");
           for ($i=0; $i<sizeof($theme_arr); $i++) {
                if ($theme_arr[$i] == $user_theme) { continue; }
                print '<option value="'.$theme_arr[$i].'">'.$theme_arr[$i].'</option>';
           }

echo '

               </select>
               <br><br>
             </td>
             <td class="userinfo" align="center"><h3>CONTACT INFO</h3><br>
               Full Name (Optional):<br><input type="text" name="full_name" value="'.$fullname.'" size="35"><br>
               Email (Optional):<br><input type="text" name="email" value="'.$email.'" size="35"><br><br>
             </td>
           </tr>
           <tr>
             <td class="userinfo" align="center" colspan="2">
             <input type="hidden" name="changeyou" value="1">
	     <font size="2"><i>NOTE: Leave fields blank if no change is necessary.</i></font><br><br>
             <input type="submit" name="submit" value="SUBMIT CHANGES">
             </td>
           </tr>
         </table>
         </form>
         <p>&nbsp;</p>
	';
} else {
print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=index.php">';
}

include_once("footer.php");

?>
