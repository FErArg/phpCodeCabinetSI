<?php

include_once("header.php");

if ($_SESSION['isloggedin'] != $glbl_hash) {
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=user.php">';
  exit; // Redirect browser and skip the rest
}

// User must be authenticated (above), so we can move on


function check_sub_owners($cid,$owner,$cnt) {
GLOBAL $prefix;
// Recursive function to check for categories or snippets not
// owned by the user who is currently logged in.

   $query1 = db_query("SELECT sid FROM ".$prefix."snippets WHERE (category_id='$cid' AND owner_id<>'$owner')");
   $num_rows1 = db_num_rows($query1);

   $query2 = db_query("SELECT cid FROM ".$prefix."categories WHERE (parent_id='$cid' AND owner_id<>'$owner')");
   $num_rows2 = db_num_rows($query2);

   $query3 = db_query("SELECT cid FROM ".$prefix."categories WHERE parent_id='$cid'");
   $num_rows3 = db_num_rows($query3);

   $num_rows = $num_rows1 + $num_rows2;
   if ($num_rows) {
     $cnt += $num_rows;
   } else if ($num_rows3) {
     while ($r = db_fetch_array($query3)) {
            $cid_next = $r["cid"];
            $cnt = check_sub_owners($cid_next,$owner,$cnt);
     }
   }

return $cnt;
}


function trace_orphans($del_cid) {
	GLOBAL $prefix,$count,$cat_array;
	// Recursive function to remove orphan categories and/or snippets upon deletion of parent category
	// Returns array of categories to be deleted.
	$remove_snippets = db_query("DELETE FROM ".$prefix."snippets WHERE category_id='$del_cid'");
	$query_category = db_query("SELECT cid FROM ".$prefix."categories WHERE parent_id='$del_cid'");
	$num_sub_categories = db_num_rows($query_category);
	if (!$count) { $count = 0; }
	if ($num_sub_categories > 0) {
	    while($r = db_fetch_array($query_category)) {
	        $cat_array[$count] = $r["cid"];
	        $count++;
		$again = trace_orphans($r["cid"]);
	    }
	}

return $cat_array;
}



// Create new category, and check for required variables
if (($_POST['submit'] == "Create Category") && ($_POST['submittype'] == "create") && ($_POST['category_name'])) {

  if (($_POST['parent_id']) && ($_POST['confirm_subcategory'] != 1)) {
    $parent_id = 0;
    $redir_cid = $parent_id;
  } else {
    $parent_id = $_POST['parent_id'];
    $redir_cid = $parent_id;
  }

  $query = "INSERT INTO ".$prefix."categories (category_name, parent_id, description, owner_id)VALUES ('".$_POST['category_name']."','$parent_id','".$_POST['category_description']."','".$_SESSION['userid']."')";
  $result = db_query($query);

  unset($_POST['submit'],$_POST['submittype'],$_POST['category_name'],$_POST['category_description'],$parent_id,$_POST['parent_id']);

  if ($_POST['camefrom'] == 'b') {
    print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php?cid='.$redir_cid.'">';
    exit; // Redirect browser and skip the rest
  }

}


// Modify existing category, and check for required variables
if (($_POST['submit'] == "Modify Category") && ($_POST['submittype'] == "modify") && ($_POST['category_name'])) {

  if (($_POST['parent_id']) && ($_POST['confirm_subcategory'] != 1)) {
    $parent_id = 0;
  } else {
    $parent_id = $_POST['parent_id'];
  }

  $query = "UPDATE ".$prefix."categories SET category_name='".$_POST['category_name']."',parent_id='$parent_id',description='".$_POST['category_description']."' WHERE cid='".$_POST['category_id']."'";
  $result = db_query($query);

  $redir_cid = $_POST['category_id'];

  unset($_POST['submit'],$_POST['submittype'],$_POST['category_name'],$_POST['category_description'],$parent_id,$_POST['parent_id'],$_POST['category_id']);

  if ($_POST['camefrom'] == 'b') {
    print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php?cid='.$redir_cid.'">';
    exit; // Redirect browser and skip the rest
  }

}

##### Functions above #####
####### Forms Below #######

if (($_GET['del'] == 1) && ($_GET['cid']) && (($_SESSION['isloggedin'] == $glbl_hash) || ($_SESSION['isadmin'] == $glbl_hash))) {

  $redirect_cid = strip_tags($_GET['cid']);
  $redirect_cf = strip_tags($_GET['cf']);
  $redirect_rfd = strip_tags($_GET['rfd']);

  $query = db_query("SELECT owner_id FROM ".$prefix."categories WHERE cid='".$_GET['cid']."'");
  list($owner_id) = db_fetch_array($query);

  if (($_SESSION['isadmin'] == $glbl_hash) || ($owner_id == $_SESSION['userid'])) {

    echo "<br><center>
          <b>Are you sure you want to delete this category? [ <a href='category.php?cid=".$redirect_cid."&del=1&cf=".$redirect_cf."&rfd=".$redirect_rfd."&confirm=1'>Y</a> / <a href='' onClick='javascript:self.close();'>N</a> ]</b><br>
	  <h3 class='info'>WARNING: Deleting this category will also delete any subcategories and/or snippets under it (RECURSIVELY).</h3>
          </center>
         ";

    if ($_GET['confirm'] == 1) {
      $query = db_query("DELETE FROM ".$prefix."categories WHERE cid='".$_GET['cid']."'");
      $cat_array = trace_orphans($redirect_cid);
      $sizeof_cat_array = sizeof($cat_array);
      for ($x=0; $x<$sizeof_cat_array; $x++) {
        $query = db_query("DELETE FROM ".$prefix."categories WHERE cid='".$cat_array[$x]."'");
      }

      echo '<body onLoad="javascript:window.opener.location.replace(\'browse.php?cid='.$redirect_rfd.'\');self.close();">';
    } else {
      unset($redirect_cf,$redirect_rfd);
    }
  }

  unset($owner_id);

  if ($redirect_cf == 'b') {
    print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=browse.php">';
    exit; // Redirect browser and skip the rest
  }

}


if ((($_GET['cid'] > 0) && ($_GET['sub'] == 1)) || (($_GET['cid'] == 0) && ($_GET['tl'] == 1))) {
  // Request must have come from browse.php
  $in_submit = "Create Category";
  $in_category_id = $_GET['cid'];
} else if ($_POST['submit'] == "Create Category") {
  // Request must have come from POST
  $in_submit = $_POST['submit'];
  $in_category_id = 0;
}

if (($in_submit == "Create Category") && (!$_POST['submittype'])) {
// Create category form page
echo '
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="1" cellspacing="2" cellpadding="3" align="center">
  <form action="'.$_SERVER['PHP_SELF'].'" method="post">
    <tr valign="top">
      <td class="categoryadmintitle" align="center">Category Administration</td>
    </tr>
    <tr valign="top">
      <td class="categoryadminmenu" align="center">

        <table width="100%" border="0" cellspacing="2" cellpadding="3" align="center">
          <tr>
            <td valign="top" align="right" width="200">Category Name:</td>
            <td valign="top" align="left" width="200"><input type="text" name="category_name" size="25" maxlength="100"></td>
          </tr>
          <tr>
            <td valign="top" align="right" width="200">Category Description:</td>
            <td valign="top" align="left" width="200"><textarea name="category_description" cols="25" rows="7" wrap="VIRTUAL"></textarea></td>
          </tr>
          <tr>
            <td align="center" colspan="2"><hr></td>
          </tr>
          <tr>
            <td valign="middle" align="right" width="200">
     ';

if ($in_category_id) {
  echo '<input type="checkbox" name="confirm_subcategory" value="1" checked>';
} else {
  echo '<input type="checkbox" name="confirm_subcategory" value="1">';
}

echo '
            </td>
            <td valign="middle" align="left" width="200">CREATE AS SUBCATEGORY</td>
          </tr>
          <tr>
            <td valign="top" align="right" width="200">Subcategory Of:</td>
            <td valign="top" align="left" width="200">
              <select name="parent_id" size="5">
     ';

if ($in_category_id) {

  $query = "SELECT cid,category_name FROM ".$prefix."categories WHERE cid='$in_category_id' ORDER BY category_name ASC";
  $result = db_query($query);
  list($cid1,$category_name1) = db_fetch_array($result);
  print "<option value=\"$cid1\" selected>$category_name1</option>";

}

$query = "SELECT cid,category_name FROM ".$prefix."categories ORDER BY category_name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
        $cid2 = $r["cid"];
        $category_name2 = $r["category_name"];
        if ($cid2 == $cid1) { continue; }
        print "<option value=\"$cid2\">$category_name2</option>";
}

echo '
              </select>
              <br><br>
            </td>
          </tr>
          <tr>
            <input type="hidden" name="submittype" value="create">
            <input type="hidden" name="camefrom" value="'.$_GET['cf'].'">
            <td valign="top" align="center" colspan="2">
            <input type="submit" name="submit" value="Create Category">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </form>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
      ';
} // end if create new entry
#-----------------------

if (($_GET['cid'] > 0) && ($_GET['browse'] == 1)) {
  // Request must have come from browse.php
  $in_submit = "Modify Category";
  $in_category_id = $_GET['cid'];
} else {
  // Request must have come from POST
  $in_submit = $_POST['submit'];
  $in_category_id = $_POST['category_id'];
}

if (($in_submit == "Modify Category") && (!$_POST['submittype']) && ($in_category_id)) {
// Modify category form page

$check_for_subs = check_sub_owners($in_category_id,$_SESSION['userid'],0);
if (($check_for_subs > 0) && ($_SESSION['isadmin'] != $glbl_hash)) { exit; } //assume someone is trying to malform url

$query = "SELECT category_name,parent_id,description FROM ".$prefix."categories WHERE cid='$in_category_id'";
$result = db_query($query);
list($category_name,$parent_id,$category_description) = db_fetch_array($result);

$query2 = "SELECT cid,category_name FROM ".$prefix."categories WHERE cid='$parent_id'";
$result2 = db_query($query2);
list($parent_cid,$parent_category_name) = db_fetch_array($result2);

echo '
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="1" cellspacing="2" cellpadding="3" align="center">
  <form action="'.$_SERVER['PHP_SELF'].'" method="post">
    <tr valign="top">
      <td class="categoryadmintitle" align="center">Category Administration</td>
    </tr>
    <tr valign="top">
      <td class="categoryadminmenu" align="center">

        <table width="100%" border="0" cellspacing="2" cellpadding="3" align="center">
          <tr>
            <td valign="top" align="right" width="200">Category Name:</td>
            <td valign="top" align="left" width="200"><input type="text" name="category_name" value="'.$category_name.'" size="25" maxlength="100"></td>
          </tr>
          <tr>
            <td valign="top" align="right" width="200">Category Description:</td>
            <td valign="top" align="left" width="200"><textarea name="category_description" cols="25" rows="7" wrap="VIRTUAL">'.$category_description.'</textarea></td>
          </tr>
          <tr>
            <td align="center" colspan="2"><hr></td>
          </tr>
          <tr>
     ';

if ($parent_id > 0) {
  print '<td valign="middle" align="right" width="200"><input type="checkbox" name="confirm_subcategory" value="1" checked></td>';
} else {
  print '<td valign="middle" align="right" width="200"><input type="checkbox" name="confirm_subcategory" value="1"></td>';
}

echo '
            <td valign="middle" align="left" width="200">MODIFY AS SUBCATEGORY</td>
          </tr>
          <tr>
            <td valign="top" align="right" width="200">Subcategory Of:</td>
            <td valign="top" align="left" width="200">
              <select name="parent_id" size="3">
     ';

if ($parent_cid) {
  print "<option value=\"$parent_cid\" selected>$parent_category_name</option>";
}

$query = "SELECT cid,category_name FROM ".$prefix."categories ORDER BY category_name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
        $select_cid = $r["cid"];
        $select_category_name = $r["category_name"];
        if ($select_category_name == $parent_category_name) { continue; }
        if ($select_category_name == $category_name) { continue; }
        print "<option value=\"$select_cid\">$select_category_name</option>";
}

echo '
              </select>
              <br><br>
            </td>
          </tr>
          <tr>
            <input type="hidden" name="category_id" value="'.$in_category_id.'">
            <input type="hidden" name="submittype" value="modify">
            <input type="hidden" name="camefrom" value="'.$_GET['cf'].'">
            <td valign="top" align="center" colspan="2">
            <input type="submit" name="submit" value="Modify Category"</td>
          </tr>
        </table>
      </td>
    </tr>
  </form>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
      ';
} // if modify entry
#-------------------



if ((!$_POST['submit']) && (!$_GET['browse']) && (!$_GET['sub'])
    && (!$_GET['tl']) && ($_SESSION['isadmin'] == $glbl_hash) && (!$_GET['cf'])) {

// Default form for category admin menu
echo '
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="1" cellspacing="2" cellpadding="3" align="center">
  <form action="'.$_SERVER['PHP_SELF'].'" method="post">
    <tr valign="top">
      <td class="categoryadmintitle" align="center">Category Administration</td>
    </tr>
    <tr valign="top">
      <td class="categoryadminmenu" align="center">
      <br>
      <input type="submit" name="submit" value="Create Category">
      <br><br>
      <input type="submit" name="submit" value="Modify Category">
      &nbsp;&nbsp;<select name="category_id" size="1">
      ';

$query = "SELECT cid,category_name FROM ".$prefix."categories ORDER BY category_name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
        $cid = $r["cid"];
        $category_name = $r["category_name"];
        print "<option value=\"$cid\">$category_name</option>";
}

echo '
        </select>
        <br><br>
      </td>
    </tr>
  </form>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
      ';

} // if no submit

include_once("footer.php");

?>
