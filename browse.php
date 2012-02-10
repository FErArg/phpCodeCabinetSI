<?php

include_once("include/header.php");

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

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

if ($_GET['msg'] == "err1") {
echo '
<p>&nbsp;</p>
<table width="90%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td align="center">
    <h3 class="err1">To enter code, first browse to the desired category or create a new category in the desired location.<br>
    Both administrators and authenticated users have rights to create new categories.</h3>
    </td>
  </tr>
</table>
     ';
}

// $cid should come in from url
$query = "SELECT * FROM ".$prefix."categories WHERE cid='".$_GET['cid']."'";
$result = db_query($query);
list($cid1,$category_name1,$parent_id1,$category_description1,$owner_id1) = db_fetch_array($result);
if ($category_description1 != "") { $category_description1 = " - ".$category_description1; }

echo '

<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="90%" border="1" cellspacing="2" cellpadding="3" align="center">
  <tr valign="top">
    <td class="browsetitle" colspan="3" valign="top" align="center">Browse by Category</td>
  </tr>
  <tr>
    <td align="left" class="categorynav">
     ';

function trace_categories($parent_id) {
        GLOBAL $built_menu,$prefix;
        // Recursive function to display parent categories
        $query = "SELECT cid,category_name,parent_id FROM ".$prefix."categories WHERE cid='$parent_id' ORDER BY category_name ASC";
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


$build_menu = trace_categories($parent_id1);

if ($build_menu) {
  $build_menu = "<a href=\"browse.php?cid=0\">BROWSE CATEGORIES</a> &gt;&gt; ".$build_menu." &gt;&gt; <a href=\"browse.php?cid=".$cid1."\">".$category_name1."</a>";
} elseif ($category_name1) {
  $build_menu = "<a href=\"browse.php?cid=0\">BROWSE CATEGORIES</a> &gt;&gt; <a href=\"browse.php?cid=".$cid1."\">".$category_name1."</a>";
} else {
  $build_menu = "<a href=\"browse.php?cid=0\">BROWSE CATEGORIES</a>";
}

echo $build_menu;


echo '</td>
      <td width="300" align="center" valign="top" rowspan="3">';

      $searchbox_type = "vertical";
      include("searchbox.php");

echo '</td>
    </tr>
    <tr valign="top">
      <td valign="top" align="left"><br>';


// Build parent categories menu above selected category
$cnt = 0;
$parent_id2 = $parent_id1;
while ($parent_id2 > 0) {
  $query = "SELECT * FROM ".$prefix."categories WHERE cid='$parent_id2' ORDER BY category_name ASC";
  $result = db_query($query);
  list($cid2,$category_name2,$parent_id2,$category_description2) = db_fetch_array($result);
  if (!$cnt) { $redirect_from_del = $cid2; }

    // Count snippets under category
    $query_snippets = "SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid2'";
    $result_snippets = db_query($query_snippets);
    $num_rows_snippets2 = db_num_rows($result_snippets);
    if ($num_rows_snippets2) { $num_snippets2 = "  ($num_rows_snippets2 Snippets)"; }

  if ($category_description2 != "") { $category_description2 = " - ".$category_description2; }
  $parent_categories = "<ul><li><a href=\"browse.php?cid=".$cid2."\">".$category_name2."</a>".$category_description2." <b>$num_snippets2</b></li>".$parent_categories;
  unset($num_rows_snippets2,$num_snippets2);
  $cnt++;
}

echo $parent_categories;

// Count snippets under category
$query_snippets = "SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid1'";
$result_snippets = db_query($query_snippets);
$num_rows_snippets1 = db_num_rows($result_snippets);
if ($num_rows_snippets1) { $num_snippets1 = "  ($num_rows_snippets1 Snippets)"; }

if ($_GET['cid'] > 0) {
  print "<ul><li><a href=\"browse.php?cid=".$cid1."\"><span style='background-color:yellow'>".$category_name1."</span></a>".$category_description1." <b>$num_snippets1</b></li>";
}

// Build categories menu below selected category
print "<ul>";

$query = "SELECT * FROM ".$prefix."categories WHERE parent_id='".$_GET['cid']."' ORDER BY category_name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
        $cid3 = $r["cid"];
        $category_name3 = $r["category_name"];
        $parent_id3 = $r["parent_id"];
        $category_description3 = $r["description"];
        if ($category_description3 != "") {
          $category_description3 = " - ".$category_description3;
        }

        // Count snippets under category
        $query_snippets = "SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid3'";
        $result_snippets = db_query($query_snippets);
        $num_rows_snippets3 = db_num_rows($result_snippets);

	// Count subcategories under category
	if ($_GET['cid'] > 0) {
          $query_categories = "SELECT cid FROM ".$prefix."categories WHERE parent_id='$cid3'";
          $result_categories = db_query($query_categories);
          $num_rows_categories3 = db_num_rows($result_categories);
	}

	if ($num_rows_snippets3 && $num_rows_categories3) {
	    $summary = "  ($num_rows_snippets3 Snippets, $num_rows_categories3 Subcategories)";
	} else if (($num_rows_snippets3) && (!$num_rows_categories3)) {
	    $summary = "  ($num_rows_snippets3 Snippets)";
	} else if (($num_rows_categories3) && (!$num_rows_snippets3)) {
	    $summary = "  ($num_rows_categories3 Subcategories)";
	}


	print "<li><a href=\"browse.php?cid=".$cid3."\">".$category_name3."</a>".$category_description3." <b>$summary</b></li>";
        unset($num_rows_snippets3,$num_snippets3,$num_categories3,$summary);
}

print "</ul>";
print "</ul>";

for ($i=0; $i<$cnt; $i++) {
  print "</ul>";
}


echo '
    <br></td>
  </tr>
  <tr>
    <td align="left">&nbsp;
     ';


if ($_SESSION['isloggedin'] == $glbl_hash) {

    $redirect_cid = strip_tags($_GET['cid']);

    if ($redirect_cid) {
        echo '<a href="input.php?cid='.$redirect_cid.'">ENTER CODE (IN THIS CATEGORY)</a> | ';
    }

    $check_for_subs = check_sub_owners($redirect_cid,$_SESSION['userid'],0);


    if ((($_GET['cid']) && ($check_for_subs == 0) && ($_SESSION['userid'] == $owner_id1)) || (($_SESSION['isadmin'] == $glbl_hash) && ($_GET['cid']))) {
    // if user owns the category and all subcategories and snippets or is an admin user

	echo '<script language=\'Javascript\'>

	        function openSmallWindow(url)  {
		    window.open(url,"smallWindow","width=300,height=200,resizable=yes");
		    return false;
		}

	      </script>';

        echo '<a href="category.php?cid='.$redirect_cid.'&browse=1&cf=b">MODIFY THIS CATEGORY</a> | <a href="category.php?cid='.$redirect_cid.'&sub=1&cf=b">ADD SUBCATEGORY</a> | <a href="browse.php?cid='.$redirect_cid.'" onClick="return(openSmallWindow(\''.$base_url.'/category.php?cid='.$redirect_cid.'&del=1&cf=b&printable=1&rfd='.$redirect_from_del.'\'))">DELETE CATEGORY</a>';
    } else if (!$_GET['cid']) {
    // if cid is not defined, it must be a top level category
        echo '<a href="category.php?cid='.$redirect_cid.'&tl=1&cf=b">ADD CATEGORY (TOP LEVEL)</a>';
    } else {
        echo '<a href="category.php?cid='.$redirect_cid.'&sub=1&cf=b">ADD SUBCATEGORY</a>';
    }

}


echo '
    </td>
  </tr>
</table>
     ';

// If snippets are found in a category, build a table to display them below
// the normal browse tables.
if ($num_snippets1) {

echo '
<br>
<table width="90%" border="1" cellspacing="2" cellpadding="3" align="center">
  <tr valign="top">
    <td width="100%" class="browsesnippets" align="left"><br>
      <ul>
     ';

$query = "SELECT * FROM ".$prefix."snippets WHERE category_id='$cid1' ORDER BY name ASC";
$result = db_query($query);
while ($r = db_fetch_array($result)) {
       $sid = $r["sid"];
       $snippet_name = $r["name"];
       $snippet_description = $r["description"];
       if ($snippet_description != "") { $snippet_description = " - ".$snippet_description; }

       print "<li><a href=\"snippet.php?sid=".$sid."\">".$snippet_name."</a>".$snippet_description."</li>";

}

echo '
      </ul><br>
    </td>
  </tr>
</table>
     ';

}

echo '
<p>&nbsp;</p>
<p>&nbsp;</p>
     ';


include_once("include/footer.php");

?>
