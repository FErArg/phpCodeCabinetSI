<?php

include_once("include/header.php");

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}


if (($_POST['addedit'] == 1) && ($_SESSION['isadmin'] == $glbl_hash)) {

    // Add a new user
    if (($_POST['add_username']) && ($_POST['add_password']) && ($_POST['add_confirm_password']) && ($_POST['add_password'] == $_POST['add_confirm_password'])) {

	$result_for_existing_users=db_query("SELECT userid FROM ".$prefix."users WHERE username='".$_POST['add_username']."'");
	$num_rows_for_existing_users=db_num_rows($result_for_existing_users);
	if ($num_rows_for_existing_users>0) {
	    $info .= "<br><h3 align='center' class='info'>Sorry, but user <i>".$_POST['add_username']."</i> already exists.</h3>";
	} else {

	    $add_password = md5($_POST['add_password']);
	    if (!$_POST['add_admin']) {
              $add_admin = "0";
            } else {
              $add_admin = $_POST['add_admin'];
            }

            $result_insert_user=db_query("INSERT INTO ".$prefix."users (username, password, fullname, email, theme, admin) VALUES('".$_POST['add_username']."','$add_password','','','$theme','$add_admin')");
            $info .= "<br><h3 align='center' class='info'>User added successfully.</h3>";

	}

    } elseif (($_POST['add_username']) && ($_POST['add_password']) && ($_POST['add_confirm_password']) && ($_POST['add_password'] != $_POST['add_confirm_password'])) {
          $info .= "<br><h3 align='center' class='info'>Sorry, the password did not match what you entered in the 'Confirm' box.</h3>";
    }


    // Change password
    if (($_POST['which_user']) && ($_POST['edit_password']) && ($_POST['edit_confirm_password']) && ($_POST['edit_password'] == $_POST['edit_confirm_password'])) {
        $edit_password = md5($_POST['edit_password']);
        $result_new_password=db_query("UPDATE ".$prefix."users SET password='$edit_password' WHERE userid='".$_POST['which_user']."'");
        $info .= "<br><h3 align='center' class='info'>Password changed successfully.</h3>";
    } elseif (($_POST['which_user']) && ($_POST['edit_password']) && ($_POST['edit_confirm_password']) && ($_POST['edit_password'] != $_POST['edit_confirm_password'])) {
        $info .= "<br><h3 align='center' class='info'>Sorry, but the new password did not match what you entered in the 'Confirm' box.</h3>";
    } elseif ((!$_POST['which_user']) && ($_POST['edit_password']) && ($_POST['edit_confirm_password']) && ($_POST['edit_password'] == $_POST['edit_confirm_password'])) {
        $info .= "<br><h3 align='center' class='info'>You did not select a user to modify.</h3>";
    }

  echo $info;

} #if addedit



if (($_POST['grantrevoke'] == 1) && ($_SESSION['isadmin'] == $glbl_hash)) {

    if ($_POST['submit']) {

        if (($_POST['submit'] == "<-GRANT") && ($_POST['nonadmins'])) {
	    // Update non-admin, set to admin
	    $result_grant=db_query("UPDATE ".$prefix."users SET admin=1 WHERE userid='".$_POST['nonadmins']."'");
        }

        if (($_POST['submit'] == "REVOKE->") && ($_POST['are_admins'])) {
	    // Update admin, set to non-admin
	    $result_revoke=db_query("UPDATE ".$prefix."users SET admin=0 WHERE userid='".$_POST['are_admins']."'");
        }

        if (($_POST['submit'] == "DELETE USER") && ($_POST['sure']) && (($_POST['nonadmins']) || ($_POST['are_admins']))) {
                     // Delete the user
                     if ($_POST['nonadmins']) {
                         $id = $_POST['nonadmins'];
                     } else {
                         $id = $_POST['are_admins'];
                     }
                     $result_delete=db_query("DELETE FROM ".$prefix."users WHERE userid='$id'");

        }

    }

}


if ($_SESSION['isadmin'] == $glbl_hash) {
echo '
         <p>&nbsp;</p>
         <h3 align="center">Add/Edit Users:</h3>
         <form name="addedit" action="'.$_SERVER['PHP_SELF'].'" method="POST">
         <table align="center" width="600" border="1" cellspacing="0" cellpadding="3">
           <tr valign="top">
             <td class="adminmenu" align="center"><font color="white"><b>ADD USER:</b></font><br><br>
	     Username:<br><input type="text" name="add_username" size="35"><br>
	     Password:<br><input type="password" name="add_password" size="35"><br>
	     Confirm Password:<br><input type="password" name="add_confirm_password" size="35"><br><br>
	     Grant Administrative Privileges: <input type="checkbox" name="add_admin" value="1">
	     </td>
             <td class="adminmenu" align="center"><font color="white"><b>CHANGE PASSWORD:</b></font><br><br>
	     <select name="which_user">
	       <option value="0" selected>- SELECT A USER -</option>';
		$result_get_users=db_query("SELECT userid,username FROM ".$prefix."users");
		while ($r=db_fetch_array($result_get_users)) {
		  $edit_userid=$r["userid"];
		  $edit_username=$r["username"];
		  print '<option value='.$edit_userid.'>'.$edit_username.'</option>';
		}

echo '
	     </select><br><br>
	     Change Password:<br>
	     <input type="password" name="edit_password" size="35"><br>
	     Confirm New Password:<br>
	     <input type="password" name="edit_confirm_password" size="35"><br><br>
	     </td>
	   </tr>
           <tr>
             <td class="adminmenu" align="center" colspan="2">
             <input type="hidden" name="addedit" value="1">
	     <font size="2"><i>NOTE: Leave fields blank if no change is necessary.</i></font><br><br>
             <input type="submit" name="submit" value="SUBMIT USER INFORMATION">
             </td>
           </tr>
	 </table>
	 </form>
	';

echo '
<form name="select_admins" action="'.$_SERVER['PHP_SELF'].'" method="POST">
<table align="center" width="600" border="1" cellspacing="0" cellpadding="3">
  <tr valign="top">
    <td class="adminmenu" align="center" colspan="3"><font color="white"><b>GRANT/REVOKE ADMIN PRIVILEGES:</b></font></td>
  </tr>
  <tr valign="top">
    <td class="adminmenu" align="center" width="250"><center>ADMINISTRATORS</center>
      <select name="are_admins" size="5">';

   $result_get_admins=db_query("SELECT userid,username FROM ".$prefix."users WHERE admin=1 ORDER BY username DESC");
   while ($r=db_fetch_array($result_get_admins)) {
       $userid=$r["userid"];
       $username=$r["username"];

       print '<option value='.$userid.'>'.$username.'</option>';
   }

echo '
      </select>
    </td>
    <td class="adminmenu" valign="middle" align="center" width="100">
      <input type="hidden" name="grantrevoke" value="1">
      <input type="submit" name="submit" value="<-GRANT">
      <br>
      <input type="submit" name="submit" value="REVOKE->">
      <br><br>
      <input type="submit" name="submit" value="DELETE USER"><br>
      <input type="checkbox" name="sure" value="sure">I\'m Sure.
    </td>
    <td class="adminmenu" valign="top" align="center" width="250"><center>NON-ADMINISTRATORS</center>
      <select name="nonadmins" size="5">';

   $query_get_nonadmins="SELECT userid,username FROM ".$prefix."users WHERE admin=0 ORDER BY username DESC";
   $result_get_nonadmins=db_query($query_get_nonadmins);
   while ($r=db_fetch_array($result_get_nonadmins)) {
       $userid=$r["userid"];
       $username=$r["username"];

       print '<option value='.$userid.'>'.$username.'</option>';
   }

echo '
      </select>
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
