<?php

include_once("header.php");



if (($_SESSION['isloggedin'] == $glbl_hash) || ($_SESSION['isadmin'] == $glbl_hash)) {

  if ($allow_comments == 1) {  // Configured so only authenticated users and admins can post comments

    if (($_POST['sid']) && ($_POST['confirm'] == 1) && ($_POST['comment'])) {

      $magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
      if (!$magic_quotes_gpc) {
        // Check for magic quotes, if it's Off, addslashes
        $stripped_comment = strip_tags(addslashes($_POST['comment']));
        $stripped_subject = strip_tags(addslashes($_POST['subject']));
        $stripped_email = strip_tags(addslashes($_POST['email']));

      } else { // magic quotes must be On, so just strip the tags
        $stripped_comment = strip_tags($_POST['comment']);
        $stripped_subject = strip_tags($_POST['subject']);
        $stripped_email = strip_tags($_POST['email']);

      }

      $last_modified = date("Y-m-d H:i:s", mktime());
      if ($_POST['comment_id']) {
        $query = db_query("UPDATE ".$prefix."user_comments SET subject='$stripped_subject',comment='$stripped_comment',last_modified='$last_modified',owner_email='$stripped_email' WHERE comment_id='".$_POST['comment_id']."' AND snippet_id='".$_POST['sid']."'");
      } else {
	$query = db_query("INSERT INTO ".$prefix."user_comments (snippet_id, subject, comment, last_modified, owner_name, owner_email, owner_id) VALUES('".$_POST['sid']."','$stripped_subject','$stripped_comment','$last_modified','".$_SESSION['username']."','$stripped_email','".$_SESSION['userid']."')");
      }
      echo '<body onLoad="javascript:window.opener.location.reload(true);self.close();">';

    }


    if (($_POST['sid']) && ($_POST['confirmdel']) && ($_POST['comment_id'])) {

      if ($_POST['confirmdel'] == "CONFIRM DELETION") {
        $query = db_query("DELETE FROM ".$prefix."user_comments WHERE comment_id='".$_POST['comment_id']."' AND snippet_id='".$_POST['sid']."'");
	$remove_this_comment = 1;
	echo '<body onLoad="javascript:window.opener.location.reload(true);self.close();">';
      } else {
        $canceling = 1;
	echo '<body onLoad="javascript:self.close();">';
      }

    }


    if (($_GET['sid']) && ($_GET['comment_id']) && ($_GET['edit'] == 1) && ($_SESSION['userid'])) {

      $query = db_query("SELECT subject,comment,owner_email,owner_id FROM ".$prefix."user_comments WHERE comment_id='".$_GET['comment_id']."'");
      list($subject,$comment,$owner_email,$owner_id) = db_fetch_array($query);
      if (($_SESSION['userid'] == $owner_id) || ($_SESSION['isadmin'] == $glbl_hash)) {
        $edit_comment_id = $_GET['comment_id'];
	$edit_subject = $subject;
	$edit_comment = $comment;
	$edit_owner_email = $owner_email;
	unset($subject,$comment,$owner_email,$owner_id);
      }

    } else if (($_GET['sid']) && ($_GET['comment_id']) && ($_GET['delete'] == 1) && ($_SESSION['userid'])) {

      $remove_this_comment = 1;

    }

    if (($remove_this_comment == 1) || ($canceling == 1)) {

      echo '<p>&nbsp;</p>
            <form action="comments.php?printable=1" method="post">
            <center>
	      <b>Are you sure you want to delete this comment?</b><br><br>
              <input type="hidden" name="sid" value="'.strip_tags($_GET['sid']).'">
              <input type="hidden" name="comment_id" value="'.$_GET['comment_id'].'">
              <input type="submit" name="confirmdel" value="CONFIRM DELETION"><br><br>
	      <input type="submit" name="confirmdel" value="CANCEL">
	    </center>
	    </form>
           ';

    } else {

      echo '

      <table width="100%" border="1" cellpadding="2">
      <form action="comments.php?printable=1" method="post">
        <tr>
          <td width="200" align="center" valign="top" class="commentwindow">

	    <table align="center" cellpadding="3" cellspacing="0">
	      <tr>
	        <td valign="top" align="center">
	          <b>SUBJECT:</b><br><input type="text" name="subject" size="25" maxlength="100" tabindex="1" class="comment" value="'.$edit_subject.'"><br><br>
		  <b>USERNAME: <i>'.$_SESSION['username'].'</i></b><br><br>
		  <b>EMAIL (Optional):</b><br><input type="text" name="email" size="25" tabindex="3" class="comment" value="'.$edit_owner_email.'"><br><br>
		  <input type="hidden" name="sid" value="'.$_GET['sid'].'">
		  <input type="hidden" name="comment_id" value="'.$_GET['comment_id'].'">
	          <input type="hidden" name="confirm" value="1"><br><br><br>
                  <input type="submit" name="submit" tabindex="5" value="SUBMIT">
                </td>
	      </tr>
	    </table>

          <td valign="top" align="center" class="commentwindow">
            <textarea rows="15" cols="45" name="comment" tabindex="4" class="comment">'.$edit_comment.'</textarea>
	  </td>
        </tr>
      </form>
      </table>

         ';

    } //end if remove

  }

}


if ($allow_comments >= 2) { // Configured so anyone can post comments

    if (($_POST['sid']) && ($_POST['confirm'] == 1) && ($_POST['comment'])) {

      $magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
      if (!$magic_quotes_gpc) {
        // Check for magic quotes, if it's Off, addslashes
        $stripped_comment = strip_tags(addslashes($_POST['comment']));
        $stripped_subject = strip_tags(addslashes($_POST['subject']));
        $stripped_username = strip_tags(addslashes($_POST['username']));
        $stripped_email = strip_tags(addslashes($_POST['email']));

      } else { // magic quotes must be On, so just strip the tags
        $stripped_comment = strip_tags($_POST['comment']);
        $stripped_subject = strip_tags($_POST['subject']);
        $stripped_username = strip_tags($_POST['username']);
        $stripped_email = strip_tags($_POST['email']);

      }

      $last_modified = date("Y-m-d H:i:s", mktime());
      if ($_POST['comment_id']) {
        $query = db_query("UPDATE ".$prefix."user_comments SET subject='$stripped_subject',comment='$stripped_comment',last_modified='$last_modified',owner_name='$stripped_username',owner_email='$stripped_email' WHERE comment_id='".$_POST['comment_id']."' AND snippet_id='".$_POST['sid']."'");
      } else {
	$query = db_query("INSERT INTO ".$prefix."user_comments (snippet_id, subject, comment, last_modified, owner_name, owner_email, owner_id) VALUES('".$_POST['sid']."','$stripped_subject','$stripped_comment','$last_modified','$stripped_username','$stripped_email','".$_SESSION['userid']."')");
      }
      echo '<body onLoad="javascript:window.opener.location.reload(true);self.close();">';

    }


    if (($_POST['sid']) && ($_POST['confirmdel']) && ($_POST['comment_id'])) {

      if ($_POST['confirmdel'] == "CONFIRM DELETION") {
        $query = db_query("DELETE FROM ".$prefix."user_comments WHERE comment_id='".$_POST['comment_id']."' AND snippet_id='".$_POST['sid']."'");
	$remove_this_comment = 1;
	echo '<body onLoad="javascript:window.opener.location.reload(true);self.close();">';
      } else {
        $canceling = 1;
	echo '<body onLoad="javascript:self.close();">';
      }

    }


    if (($_GET['sid']) && ($_GET['comment_id']) && ($_GET['edit'] == 1) && ($_SESSION['userid'])) {

      $query = db_query("SELECT subject,comment,owner_name,owner_email,owner_id FROM ".$prefix."user_comments WHERE comment_id='".$_GET['comment_id']."'");
      list($subject,$comment,$owner_name,$owner_email,$owner_id) = db_fetch_array($query);
      if (($_SESSION['userid'] == $owner_id) || ($_SESSION['isadmin'] == $glbl_hash)) {
        $edit_comment_id = $_GET['comment_id'];
	$edit_subject = $subject;
	$edit_comment = $comment;
	$edit_owner_name = $owner_name;
	$edit_owner_email = $owner_email;
	unset($subject,$comment,$owner_name,$owner_email,$owner_id);
      }

    } else if (($_GET['sid']) && ($_GET['comment_id']) && ($_GET['delete'] == 1) && ($_SESSION['userid'])) {

      $remove_this_comment = 1;

    }


    if (($remove_this_comment == 1) || ($canceling == 1)) {

      echo '<p>&nbsp;</p>
            <form action="comments.php?printable=1" method="post">
            <center>
	      <b>Are you sure you want to delete this comment?</b><br><br>
              <input type="hidden" name="sid" value="'.$_GET['sid'].'">
              <input type="hidden" name="comment_id" value="'.$_GET['comment_id'].'">
              <input type="submit" name="confirmdel" value="CONFIRM DELETION"><br><br>
	      <input type="submit" name="confirmdel" value="CANCEL">
	    </center>
	    </form>
           ';

    } else {

      echo '

      <table width="100%" border="1" cellpadding="2">
      <form action="comments.php?printable=1" method="post">
        <tr>
          <td width="200" align="center" valign="top" class="commentwindow">

	    <table align="center" cellpadding="3" cellspacing="0">
	      <tr>
	        <td valign="top" align="center">
	          <b>SUBJECT:</b><br><input type="text" name="subject" size="25" maxlength="100" tabindex="1" class="comment" value="'.$edit_subject.'"><br><br>
		  <b>NAME (Optional):</b><br><input type="text" name="username" size="25" tabindex="2" value="'.$edit_owner_name.'" class="comment"><br><br>
		  <b>EMAIL (Optional):</b><br><input type="text" name="email" size="25" tabindex="3" class="comment" value="'.$edit_owner_email.'"><br><br>
		  <input type="hidden" name="sid" value="'.$_GET['sid'].'">
		  <input type="hidden" name="comment_id" value="'.$edit_comment_id.'">
	          <input type="hidden" name="confirm" value="1"><br><br><br>
                  <input type="submit" name="submit" tabindex="5" value="SUBMIT">
                </td>
	      </tr>
	    </table>

          <td valign="top" align="center" class="commentwindow">
            <textarea rows="15" cols="45" name="comment" tabindex="4" class="comment">'.$edit_comment.'</textarea>
	  </td>
        </tr>
      </form>
      </table>

         ';
	 
    } //end if remove

}


if ($allow_comments == 0) { // Configured so anyone can post comments

  echo '

  <table width="100%" border="0" align="center" valign="middle">
    <tr>
      <td width="100%" align="center">

      <p><b>Snippet comments are currently disabled on this server.</b></p>

      </td>
    </tr>
  </table>

       ';

}

include_once("footer.php");

?>
