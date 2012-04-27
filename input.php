<?php

include_once("header.php");
// include("framework.php");

/*
echo "<pre>";
print_r($_POST);
print_r($_GET);
echo "</pre>";
*/


if ($_SESSION['isloggedin'] != $glbl_hash) {
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=user.php">';
  exit; // Redirect browser and skip the rest
}
// User must be authenticated (above), so we can move on

// SerInformaticos
if( isset($_POST['snippet']) ){
	$_POST['snippet'] = limpiarTexto1($_POST['snippet']);
}
foreach( $_GET as $key => $value ){
	$_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
}
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

/*
echo "<pre>";
print_r($_POST);
echo "---<br />";
print_r($_GET);
echo "</pre>";
*/

if (!$_GET['cid']) {
  // User must select category before entering code
  // Pass script to browse.php with err variable
  $msg = "err1";
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php?msg=err1">';
  exit;
}

/*
function list_files($dir) {
// Returns array of file names from $dir

  $i=0;
  if ($use_dir = @opendir($dir)) {
    while (($file = readdir($use_dir)) !== false) {
         if (ereg("HFile_",$file)) {
	     $file = ereg_replace("HFile_","",$file);
	     $file = ereg_replace(".php","",$file);
             $syntax_type_arr[$i] = "$file";
             $i++;
         }
    }
    closedir($use_dir);
  }

return $syntax_type_arr;
}
*/
// modificacion de snnipet
if (($_GET['change'] == 1) && ($_GET['sid']) && ($_SESSION['isloggedin'])) {

  $result = db_query("SELECT sid,name,description,comment,author_name,author_email,language,highlight_mode,owner_id,snippet FROM ".$prefix."snippets WHERE sid='".$_GET['sid']."'");
  list($mod_sid,$mod_name,$mod_description,$mod_comment,$mod_author_name,$mod_author_email,$mod_language,$mod_highlight_mode,$mod_owner_id,$mod_snippet) = db_fetch_array($result);

  if ($mod_owner_id != $_SESSION['userid']) {
      if ($_SESSION['isadmin'] != $glbl_hash) {
          unset($mod_sid,$mod_name,$mod_description,$mod_comment,$mod_author_name,$mod_author_email,$mod_language,$mod_highlight_mode,$mod_owner_id,$mod_snippet);
	  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php">';
      }
  }

echo "<pre>";
echo "id ".$mod_sid."<br />";
echo "name ".$mod_name."<br />";
echo "desc ".$mod_description."<br />";
echo "comm ".$mod_comment."<br />";
echo "auth ".$mod_author_name."<br />";
echo "mail ".$mod_author_email."<br />";
echo "lang ".$mod_language."<br />";
echo "high ".$mod_highlight_mode."<br />";
echo "owner_id ".$mod_owner_id."<br />";
echo "snip ".$mod_snippet."<br />";
echo "</pre>";

  $magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
  if ($magic_quotes_gpc) {
    // Check for magic quotes, if it's On, stripslashes
    $mod_snippet_name = stripslashes($mod_snippet_name);
    $mod_description = stripslashes($mod_description);
    $mod_comment = stripslashes($mod_comment);
    $mod_author_name = stripslashes($mod_author_name);
    $mod_language = stripslashes($mod_language);
    $mod_snippet = stripslashes($mod_snippet);
  }

} else if ((!$_GET['change']) && ($_SESSION['isloggedin'])) {

  $mod_author_name = $_SESSION['fullname'];
  $mod_author_email = $_SESSION['user_email'];

}


// Upon submission, check for required variables
if ($_POST['submit'] && $_POST['snippet'] && $_POST['snippet_name'] && $_POST['category_id']) {

  // Make sure author's email is okay to publish (nobody likes spam)
  if ($_POST['author_email'] && !$_POST['permission']) {
    $author_email = "";
  } else {
    $author_email = $_POST['author_email'];
  }

  // Check for unclassified language
  if (!$_POST['highlight_mode']) {
    $highlight_mode = "Unlisted";
  } else {
    $highlight_mode = $_POST['highlight_mode'];
  }

  	if( empty($_POST['language']) OR $_POST['language'] == ' ' ){
		$language = $_POST['highlight_mode'];
		$_POST['language'] = $_POST['highlight_mode'];
	}

  $redirect = $_POST['category_id'];

  // Create date for datetime format
  $last_modified = date("Y-m-d H:i:s", mktime());

  $magic_quotes_gpc = (bool) ini_get('magic_quotes_gpc');
  if (!$magic_quotes_gpc) {

    // Check for magic quotes, if it's Off, addslashes
    $stripped_snippet_name = strip_tags(addslashes($_POST['snippet_name']), $allowed_html_tags);
    $stripped_description = strip_tags(addslashes($_POST['description']), $allowed_html_tags);
    $stripped_comment = strip_tags(addslashes($_POST['comment']), $allowed_html_tags);
    $stripped_author_name = strip_tags(addslashes($_POST['author_name']), $allowed_html_tags);
    $stripped_language = strip_tags(addslashes($_POST['language']), $allowed_html_tags);
    $stripped_snippet = addslashes($_POST['snippet']);

  } else { // magic quotes must be On, so just strip the tags
    $stripped_snippet_name = strip_tags($_POST['snippet_name'], $allowed_html_tags);
    $stripped_description = strip_tags($_POST['description'], $allowed_html_tags);
    $stripped_comment = strip_tags($_POST['comment'], $allowed_html_tags);
    $stripped_author_name = strip_tags($_POST['author_name'], $allowed_html_tags);
    $stripped_language = strip_tags($_POST['language'], $allowed_html_tags);
    $stripped_snippet = $_POST['snippet'];

  }

  $stripped_snippet = str_replace(chr(0x09),"     ",$stripped_snippet); // replace tabs with five spaces

  if ($_POST['sid']) {
	  // Actualiza snippet
    $update = db_query("UPDATE ".$prefix."snippets SET name='$stripped_snippet_name', description='$stripped_description', comment='$stripped_comment', author_name='$stripped_author_name', author_email='".strip_tags($author_email)."', language='$stripped_language', highlight_mode='$highlight_mode', category_id='".$_POST['category_id']."', last_modified='$last_modified', snippet='$stripped_snippet' WHERE sid='".$_POST['sid']."'");

  } else {
	// Agrega snippet
		$insert = db_query("INSERT INTO ".$prefix."snippets (name, description, comment, author_name, author_email, language, highlight_mode, category_id, last_modified, owner_id, snippet)
			VALUES ('$stripped_snippet_name','$stripped_description','$stripped_comment','$stripped_author_name','$author_email','$stripped_language','$highlight_mode','".$_POST['category_id']."','$last_modified','".$_SESSION['userid']."','$stripped_snippet')");

/*
	echo "<pre>";
	echo "name ".$stripped_snippet_name."<br />";
	echo "desc ".$stripped_description."<br />";
	echo "comm ".$stripped_comment."<br />";
	echo "auth ".$stripped_author_name."<br />";
	echo "mail ".$author_email."<br />";
	echo "lang ".$stripped_language."<br />";
	echo "high ".$highlight_mode."<br />";
	echo "snip ".$stripped_snippet."<br />";
	echo "</pre>";

	echo mysql_error();
*/
  }

  unset($_POST['submit'],$_POST['snippet'],$_POST['snippet_name'],$_POST['language'],$language,$_POST['highlight_mode'],$highlight_mode,$_POST['category_id'],$_POST['author_email'],$author_email,$_POST['permission'],$last_modified,$owner_id);

  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php?cid='.$redirect.'">';

  exit;

} // end check for required variables


##### Functions above #####
####### Forms Below #######

echo '
<p>&nbsp;</p>
<table width="90%" border="0" cellspacing="2" cellpadding="3" align="center">
  <form action="'.$_SERVER['PHP_SELF'].'?cid='.strip_tags($_GET['cid']).'" method="post">
    <tr valign="top">
      <td align="left" colspan="2">
        <table border="1" width="350" align="center" cellpadding="5" cellspacing="0">
          <tr>
            <td class="newcodeentry" align="center">
              <h3>New Code Entry:</h3>
              Enter all relevant information pertaining to your code snippet.  If you are not the original author of the code, please provide the original author\'s name with the code, and be certain that you have permission to use the code.  If you wish to enter an email address for the author, please request permission from the author before doing so.
              <br><br>
            </td>
          </tr>
        </table>
        <br>
      </td>
      <td align="left" rowspan="11">ENTER CODE SNIPPET BELOW <font color="red">(Required)</font>:<br>
        <textarea name="snippet" cols="85" rows="50" wrap="off" tabindex="10">'.$mod_snippet.'</textarea>
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110"> SNIPPET NAME <font color="red">(Required)</font>: </td>
      <td align="left" width="250">
        <input type="text" value="'.$mod_name.'" name="snippet_name" size="25" maxlength="100" tabindex="1">
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">DESCRIPTION: </td>
      <td align="left" width="250">
        <textarea name="description" cols="25" rows="7" wrap="VIRTUAL" tabindex="2">'.$mod_description.'</textarea>
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">AUTHOR\'S NAME: </td>
      <td align="left" width="250">
        <input type="text" value="'.$mod_author_name.'" name="author_name" size="25" maxlength="100" tabindex="3">
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">AUTHOR\'S EMAIL: </td>
      <td align="left" width="250">
        <input type="text" value="'.$mod_author_email.'" name="author_email" size="25" maxlength="100" tabindex="4">
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110"><font size="2">';

      if ($mod_author_email) {
        echo '<input type="checkbox" name="permission" value="1" tabindex="5" checked>';
      } else {
        echo '<input type="checkbox" name="permission" value="1" tabindex="5">';
      }

echo '
        </font></td>
      <td align="left" width="250"><font size="2">The author has granted permission<br>
        to publish his/her email address.</font></td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">HIGHLIGHT MODE: </td>
      <td align="left" width="250">
      <select name="highlight_mode" size="1" tabindex="6">';

$highlightMode = array(
//			"a"	=> "ada95",
//			"ada"	=> "ada95",
//			"adb"	=> "ada95",
//			"ads"	=> "ada95",
//			"asm"	=> "asm_x86",
			"asp"	=> "asp",
			"awk"	=> "awk",
//			"bas"	=> "vb|vbdotnet",
			"bash"	=> "bash",
			"c"	=> "c",
//			"cbl"	=> "cobol",
//			"cls"	=> "vb|vbdotnet",
//			"cob"	=> "cobol",
//			"cpy"	=> "cobol",
//			"cpp"	=> "cpp",
//			"cs"	=> "csharp",
//			"cxx"	=> "cpp",
//			"dat"	=> "mumps",
//			"dpr"	=> "delphi",
//			"e"	=> "eiffel|euphoria",
//			"ew"	=> "euphoria",
//			"eu"	=> "euphoria",
//			"ex"	=> "euphoria",
//			"exw"	=> "euphoria",
//			"exu"	=> "euphoria",
//			"frm"	=> "vb|vbdotnet",
//			"h"	=> "c",
//			"hpp"	=> "cpp",
			"html"	=> "html",
			"html5"	=> "html5",
//			"inc"	=> "turbopascal|vb|vbdotnet",
			"java"	=> "java",
			"js"	=> "javascript",
//			"lsp"	=> "lisp",
//			"m"	=> "mumps",
//			"pas"	=> "delphi|turbopascal",
			"php"	=> "php",
			"php3"	=> "php3",
			"php4"	=> "php4",
			"php5"	=> "php5",
			"pl"	=> "perl",
//			"pm"	=> "perl",
			"py"	=> "python",
//			"pyc"	=> "python",
//			"rtn"	=> "mumps",
//			"scm"	=> "scheme",
//			"vb"	=> "vb|vbdotnet",
//			"vbs"	=> "vb|vbdotnet|vbscript",
//			"wsf"	=> "vbscript"
			);

// SerInformaticos
foreach ( $highlightMode as $key => $value ) {
	if( $key == $mod_highlight_mode ){
		echo "<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n";
	} else{
		echo "<option value=\"".$value."\" >".$value."</option>\n";
	}
}
/*
foreach ( $highlightMode as $key => $value ){
	// echo '<option value="'.$value.'" selected>'.$value.'</option>';
	echo '<option value="'.$value.'" >'.$value.'</option>';
}
*/
/*
$lang_types = list_files($HFile_dir);
usort($lang_types, 'strcasecmp');
$sizeof_lang_types = sizeof($lang_types);
for ($i=0; $i<$sizeof_lang_types; $i++) {
  if ($lang_types[$i] == $mod_highlight_mode) { continue; }
  print '<option value="'.$lang_types[$i].'">'.$lang_types[$i].'</option>';
}
*/
echo '
        </select>
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">LANGUAGE: </td>
      <td align="left" width="250">
        <input type="text" value="'.$mod_language.'" name="language" size="25" maxlength="100" tabindex="7">
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">CATEGORY: </td>
      <td align="left" width="250">
        <select name="category_id" size="1"  tabindex="8">';


$query = "SELECT cid,category_name FROM ".$prefix."categories WHERE cid='".$_GET['cid']."'";
$result = db_query($query);
list($selected_cid,$selected_name) = db_fetch_array($result);
print "<option value=\"$selected_cid\" selected>$selected_name</option>";

$query = "SELECT cid,category_name FROM ".$prefix."categories ORDER BY category_name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
        $cid = $r["cid"];
        $category_name = $r["category_name"];
        if ($cid == $selected_cid) { continue; }
        print "<option value=\"$cid\">$category_name</option>";
}

echo '
        </select>
      </td>
    </tr>
    <tr valign="top">
      <td align="right" width="110">COMMENT(S):</td>
      <td align="left" width="250">
        <textarea name="comment" cols="25" rows="7" wrap="VIRTUAL" tabindex="9">'.$mod_comment.'</textarea>
      </td>
    </tr>
    <tr valign="top">
      <td align="center" colspan="2">
      <input type="hidden" value="'.$mod_sid.'" name="sid">
      <input type="submit" value="Submit Code Snippet Now" name="submit" tabindex="11"><br>
      </td>
    </tr>
  </form>
</table>
<p>&nbsp;</p>
      ';

include_once("footer.php");

?>
