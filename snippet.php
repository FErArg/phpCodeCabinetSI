<?php

include_once("header.php");

if ($_SESSION['isloggedin'] != $glbl_hash) {
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=user.php">';
  exit; // Redirect browser and skip the rest
}

$result = db_query("SELECT sid,name,description,comment,author_name,author_email,language,highlight_mode,category_id,last_modified,owner_id,snippet FROM ".$prefix."snippets WHERE sid='".$_GET['sid']."'");
list($sid,$name,$description,$comment,$author_name,$author_email,$language,$highlight_mode,$category_id,$last_modified,$owner_id,$snippet) = db_fetch_array($result);

$magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
if ($magic_quotes_gpc) {
    // Check for magic quotes, if it's On, stripslashes
    $snippet_name = stripslashes($snippet_name);
    $description = stripslashes($description);
    $comment = stripslashes($comment);
    $author_name = stripslashes($author_name);
    $language = stripslashes($language);
    //$snippet = stripslashes($snippet);
}



if ($author_email) { $author_email = "&lt;<a href=\"mailto:$author_email\">$author_email</a>&gt;"; }


// Delete a snippet
if (($_GET['del'] == 1) && (($owner_id == $_SESSION['userid']) || ($_SESSION['isadmin'] == $glbl_hash))) {

  if ($_GET['confirm'] == 1) {

      $query = db_query("DELETE FROM ".$prefix."snippets WHERE sid='$sid'");
      print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php">';
  } else {
      echo "<p>&nbsp;</p><center>
            <b>Are you sure you want to delete this entry? [ <a href='snippet.php?sid=$sid&del=1&confirm=1'>Y</a> / <a href='snippet.php?sid=$sid'>N</a> ]</b>
            </center>
           ";
  }

}

/*
require_once "include/Beautifier/Init.php";
global $BEAUT_PATH;
require_once "$BEAUT_PATH/HFile/HFile_".$highlight_mode.".php";
require_once "$BEAUT_PATH/Output/Output_HTML.php";
$langobj = "HFile_".$highlight_mode;
$highlighter = new Core(new $langobj(), new Output_HTML());
*/

function trace_categories($parent_id) {
        GLOBAL $built_menu,$prefix;
        // Recursive function to display parent categories
        $query = "SELECT cid,category_name,parent_id FROM ".$prefix."categories WHERE cid='$parent_id'";
        $result = db_query($query);
        while ($r = db_fetch_array($result)) {
                $cid = $r["cid"];
                $category_name = $r["category_name"];
                $parent_id = $r["parent_id"];
                    if ($parent_id > 0) { // meaning it actually has a parent category
                      $built_menu = " &gt;&gt; <a href=\"browse.php?cid=".$cid."\">".$category_name."</a>".$built_menu;
                      $again = trace_categories($parent_id); // run it again
                    } else {
                      $built_menu = "<a href=\"browse.php?cid=".$cid."\">".$category_name."</a>".$built_menu;
                    }
          }

return $built_menu;
}


if ($_GET['printable'] != 1) { //do not format page for printing

  echo '<p>&nbsp;</p>
        <table cellpadding="3" cellspacing="2" border="1" width="90%" align="center">
          <tr>
            <td class="categorynav" valign="top" rowspan="1" colspan="2">Category:
       ';

  $query = "SELECT category_name,parent_id FROM ".$prefix."categories WHERE cid='$category_id'";
  $result = db_query($query);
  while ($r = db_fetch_array($result)) {
         $category_name = $r["category_name"];
         $parent_id = $r["parent_id"];
         $built_menu = trace_categories($parent_id);
         $built_menu .= " &gt;&gt; <a href=\"browse.php?cid=".$category_id."\">".$category_name."</a> &gt;&gt; <a href=\"snippet.php?sid=".$_GET['sid']."\">".$name."</a>";
         print $built_menu;
  }

  echo '
	    </td>
          </tr>';

  echo '<tr>
            <td class="snippetinfo" valign="top" height="50" width="300">
              <table border="0" width="250" align="center" cellpadding="0" cellspacing="0">
                <tr>
		  <td width="100%" valign="top" class="snippetinfo">
		    <br><h3 align="center">Code Snippet Information</h3>
                    <br><b>SNIPPET NAME:</b> '.$name.'<br>
                    <br><b>DESCRIPTION:</b> '.ereg_replace("\n", "<br>", $description).'<br>
                    <br><b>COMMENT(S):</b> '.ereg_replace("\n", "<br>", $comment).'<br>
                    <br><b>AUTHOR:</b> '.$author_name.' '.$author_email.'<br>
                    <br><b>LAST MODIFIED:</b> '.$last_modified.'<br>
                    <br><b>LANGUAGE:</b> '.$language.'<br>
                    <br><b>HIGHLIGHT MODE:</b> '.$highlight_mode.'<br>
                    <br><br>
                  </td>
		</tr>
              </table>
            </td>
            <td valign="top" rowspan="2" colspan="1" align="left">';

  // SerInformaticos
  // reemplaza < y >
  $snippet = str_replace('>', '&gt;', $snippet);
  $snippet = str_replace('<', '&lt;', $snippet);
  // echo $snippet;
  // echo "<p class=\"autotest\">\n<pre>\n<code>\n".$snippet."\n</code>\n</pre>\n</p><br>";
  // echo "<pre>\n<p class=\"autotest\">\n<code>\n".$snippet."\n</code>\n</p>\n</pre><br>";
  echo"<p id=\"autotest\">
			<pre><code>".$snippet."
		</p>";

  echo '</td>
	  </tr>
          <tr>
            <td class="adminmenu" valign="top" width="300">
              <br><div align="center">Snippet Options</div>
                <ul>
	 ';

  if (($owner_id == $_SESSION['userid']) || ($_SESSION['isadmin'] == $glbl_hash)) {
    echo '        <li><a href="input.php?cid='.$category_id.'&sid='.$sid.'&change=1">Modify Snippet</a></li>
                  <li><a href="snippet.php?sid='.$sid.'&del=1">Delete Snippet</a></li>
         ';
  }


  if (($_SESSION['isloggedin'] == $glbl_hash) || ($_SESSION['isadmin'] == $glbl_hash)) {

    if ($allow_comments == 1) { // Configured so only authenticated users and admins can post comments
      echo '

		  <script language=\'Javascript\'>

		  function openSmallWindow(url)  {
		    window.open(url,"smallWindow","width=600,height=290,resizable=yes");
		    return false;
		  }

		  </script>

                  <li><a href="comments.php?sid='.$sid.'" onClick="return(openSmallWindow(\''.$base_url.'/comments.php?sid='.$sid.'&printable=1\'))">Add Comment</a></li>';
    }

  }


    if ($allow_comments >= 2) { // Configured so anyone can post comments
      echo '

		  <script language=\'Javascript\'>

		  function openSmallWindow(url)  {
		    window.open(url,"smallWindow","width=600,height=290,resizable=yes");
		    return false;
		  }

		  </script>

                  <li><a href="comments.php?sid='.$sid.'" onClick="return(openSmallWindow(\''.$base_url.'/comments.php?sid='.$sid.'&printable=1\'))">Add Comment</a></li>';
    }

    echo '        <li><a href="snippet.php?sid='.$sid.'&printable=1" target="external">Printable Snippet</a></li>';

    if ($allow_comments) { // if comments are allowed, provide an option to print snippets with user comments
      echo '      <li><a href="snippet.php?sid='.$sid.'&printable=1&withcomm=1" target="external">Printable Snippet<br>(w/ Comments)</a></li>';
    }

    if (($allow_anon_export == 1) || ($_SESSION['isloggedin'] == $glbl_hash)) {
        echo '<li><a href="export.php?cid='.$category_id.'&export_snippet='.$sid.'">Add to Export List</a></li>';
    }

    echo '      </ul>
              <br>
            </td>
          </tr>
	</table>
	<p>&nbsp;</p>
	 ';


} else { //load page as printable

  echo '
        <table cellpadding="3" cellspacing="2" border="1" width="100%" align="center">
          <tr>
            <td class="categorynav" valign="top">
       ';

  if ($name) { $page_head .= $name; }
  if ($description) { $page_head .= " - ".$description; }
  print $page_head;

  echo '
            </td>
	  </tr>
	  <tr>
	    <td valign="top">
	      <pre><br>'.$highlighter->highlight_text($snippet).'<br></pre>
	    </td>
	  </tr>
	</table>
	<p>&nbsp;</p>
       ';

}


// Display comments
if ($allow_comments) { // First check to make sure comments are enabled


  if ($_GET['printable'] != 1) {
      $safe_to_print_comments = 1;
  } else if (($_GET['printable'] == 1) && ($_GET['withcomm'] == 1)) {
      $safe_to_print_comments = 1;
  }

  if ($safe_to_print_comments == 1) {

    $query = db_query("SELECT * FROM ".$prefix."user_comments WHERE snippet_id='$sid' ORDER BY comment_id ASC");
    $num_rows = db_num_rows($query);
    if ($num_rows) {
      if ($_GET['withcomm'] == 1) {
        print '<table cellpadding="3" cellspacing="2" border="1" width="100%" align="center">';
      } else {
        print '<table cellpadding="3" cellspacing="2" border="1" width="90%" align="center">';
      }
    }

    while ($r = db_fetch_array($query)) {
  	 $comment_id = $r["comment_id"];
	 $subject = $r["subject"];
	 if (!$subject) { $subject = "<i>No Subject</i>"; }
	 $comment = $r["comment"];
	 $last_modified = $r["last_modified"];
	 $owner_name = $r["owner_name"];
	 if (!$owner_name) { $owner_name = "Anonymous"; }
	 $owner_email = $r["owner_email"];
	 if ($owner_email) { $owner_email = "&lt;<a href=\"mailto:$owner_email\">".$owner_email."</a>&gt;"; }
	 $comm_owner_id = $r["owner_id"];

	 $magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
	 if ($magic_quotes_gpc) {
    	   // Check for magic quotes, if it's On, stripslashes
    	   $subject = stripslashes($subject);
    	   $comment = stripslashes($comment);
    	   $owner_name = stripslashes($owner_name);
	 }

	 echo '

	   <tr>
	     <td class="commsubject">
	       <b>'.$owner_name.'</b> '.$owner_email.'  '.$last_modified.'<br><b>Subject:</b> '.$subject.'

	       ';

          if (($comm_owner_id == $_SESSION['userid']) || ($_SESSION['isadmin'] == $glbl_hash)) {
	    //no need to reprint java functions...already displayed above
	    echo '<br>Comment Options: <a href="comments.php?sid='.$sid.'" onClick="return(openSmallWindow(\'comments.php?sid='.$sid.'&comment_id='.$comment_id.'&edit=1&printable=1\'))">EDIT</a> | <a href="comments.php?sid='.$sid.'" onClick="return(openSmallWindow(\'comments.php?sid='.$sid.'&comment_id='.$comment_id.'&delete=1&printable=1\'))">DELETE</a>';
	  }

          echo '
	     </td>
	   </tr>
	   <tr>
	     <td>
	       '.ereg_replace("\n", "<br>", $comment).'<p>&nbsp;</p>
	     </td>
	   </tr>

	      ';

    }

    if ($num_rows) { print '</table><p>&nbsp;</p>'; }

  } // if safe_to_print_comments

} // if $allow_comments




include_once("footer.php");

?>





