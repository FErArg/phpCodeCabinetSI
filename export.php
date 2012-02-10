<?php

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

// SerInformaticos
foreach( $_GET as $key => $value ){
	$_GET[$key] = filter_var($_GET[$key], FILTER_SANITIZE_STRING);
}

if ($_GET['final_export_array']) {

  $date = date("m-d-Y", mktime());
  header("Content-type: application/octet-stream");
  header("Content-disposition: attachment; filename=phpcc-$date.csv");
  session_start();
  require_once("include/config.php");

} else {
  include_once("include/header.php");
}


if (($allow_anon_export != 1) || ($_GET['allow_anon_export'])) {
// Check for anonymous exports setting to be disabled or fraudulent

  if ($_SESSION['isloggedin'] != $glbl_hash) {

        print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=user.php">';
        exit; // Redirect browser and skip the rest

  }

}


function trace_subs($top_level_cid) {
	GLOBAL $prefix;
	// Recursive function to add subordinate categories and/or snippets to the export list.
	$query_snippets = db_query("SELECT sid FROM ".$prefix."snippets WHERE category_id='$top_level_cid'");

	while ($r = db_fetch_array($query_snippets)) {
	  $next_snippets = intval(sizeof($_SESSION['export_snippets']) + 1);
	  if (!in_array($r["sid"], $_SESSION['export_snippets'])) {
	    $_SESSION['export_snippets'][$next_snippets] = $r["sid"];
	  }
	}

	$query_category = db_query("SELECT cid FROM ".$prefix."categories WHERE parent_id='$top_level_cid'");
	$num_sub_categories = db_num_rows($query_category);
	if ($num_sub_categories > 0) {
            while($r = db_fetch_array($query_category)) {
	      $next_cat = intval(sizeof($_SESSION['export_categories']) + 1);
	      if (!in_array($r["cid"], $_SESSION['export_categories'])) {
	        $_SESSION['export_categories'][$next_cat] = $r["cid"];
	      }
	      $again = trace_subs($r["cid"]);
	    }
	}
}



if ($_GET['clear_exports'] == 1) {
  session_unregister("export_snippets");
  session_unregister("export_categories");
  unset($_SESSION['export_snippets']);
  unset($_SESSION['export_categories']);
}


if ($_GET['export_snippet']) {
// Incoming additions to the export_snippets session variable
  $next = intval(sizeof($_SESSION['export_snippets']) + 1);
  if (!in_array($_GET['export_snippet'], $_SESSION['export_snippets'])) {
    $_SESSION['export_snippets'][$next] = $_GET['export_snippet'];
  }
}


if (($_GET['export_category']) && ($_GET['type'])) {
// Incoming additions to the export_categories session variable
  $next_cat = intval(sizeof($_SESSION['export_categories']) + 1);
  if ($_GET['type'] == "single") { // we do not have to query for subordinates

    if (!in_array($_GET['export_category'], $_SESSION['export_categories'])) {
      $_SESSION['export_categories'][$next_cat] = $_GET['export_category'];
    }

  } else if ($_GET['type'] == "recursive") { // we have to query subordinates

    $next_cat = intval(sizeof($_SESSION['export_categories']) + 1);
    if (!in_array($_GET['export_category'], $_SESSION['export_categories'])) {
      $_SESSION['export_categories'][$next_cat] = $_GET['export_category'];
    }
    $add_the_rest = trace_subs($_GET['export_category']);

  }


}


if ((  ($_POST['cid']) || ($_GET['cid'])) && (($_POST['confirm_export'] == 1) || ($_GET['confirm_export'] == 1))
    && (($_POST['export_file_type']) || ($_GET['export_file_type'])) && (($_SESSION['export_snippets']) || ($_SESSION['export_categories']))) {
  // Export selections have been made, and it's time to create the export file
  // Note that 'cid' is not needed, it was only used to validate the incoming form variables
  // Use all 'cid' and 'sid' variables from session

  if ($_GET['final_export_array']) {

    if ($_POST['export_file_type']) {
      $export_file_type = $_POST['export_file_type'];
    } else {
      $export_file_type = $_GET['export_file_type'];
    }

    if ($export_file_type == "csv") {

      ######### CSV File Format for Export #########

      // File format consists of the following:
      // First line is db field descriptions for rebuilding tables,
      // then comes categories, followed by snippets.

      $sizeof_export_categories = sizeof($_SESSION['export_categories']);
      for ($x=1; $x<=$sizeof_export_categories; $x++) {
        $cat_query = db_query("SELECT * FROM ".$prefix."categories WHERE cid='".$_SESSION['export_categories'][$x]."'");
	list($cid,$category_name,$parent_id,$description,$owner_id) = db_fetch_array($cat_query);
	$category_name = ereg_replace(",","[phpcc-comma]",$category_name); // This will be replaced again by import
	$description = ereg_replace(",","[phpcc-comma]",$description); // This will be replaced again by import
	if ($x > 1) { echo ","; }
        echo "$cid,$category_name,$parent_id,$description,$owner_id";
      }

      echo "\n\n";
      echo "-------0|0-------\n\n";  // This is merely a marker to divide categories from snippets upon re-importing

      $sizeof_export_snippets = sizeof($_SESSION['export_snippets']);

      for ($x=1; $x<=$sizeof_export_snippets; $x++) {
        $snippet_query = db_query("SELECT * FROM ".$prefix."snippets WHERE sid='".$_SESSION['export_snippets'][$x]."'");
	list($sid,$name,$description,$comment,$author_name,$author_email,$language,$highlight_mode,$category_id,$last_modified,$owner_id,$snippet) = db_fetch_array($snippet_query);

	$name = ereg_replace(",","[phpcc-comma]",$name); // This will be replaced again by import
	$description = ereg_replace(",","[phpcc-comma]",$description); // This will be replaced again by import
	$comment = ereg_replace(",","[phpcc-comma]",$comment); // This will be replaced again by import
	$author_name = ereg_replace(",","[phpcc-comma]",$author_name); // This will be replaced again by import
	$language = ereg_replace(",","[phpcc-comma]",$language); // This will be replaced again by import
	$snippet = ereg_replace(",","[phpcc-comma]",$snippet); // This will be replaced again by import
	$snippet = ereg_replace("\r\n","\n",$snippet);
	if ($x > 1) { echo ","; }
	echo "$sid,$name,$description,$comment,$author_name,$author_email,$language,$highlight_mode,$category_id,$last_modified,$owner_id,$snippet\n";
      }


      ##############################################

    } else if ($export_file_type == "xml") {
    // implement after csv is working
    } else if ($export_file_type == "bzip") {
    // implement after csv and xml are working
    }

    exit;

  } else {
    //build the url
    $final_export_array = '?cid='.$_POST['cid'].'&confirm_export=1&export_file_type='.$_POST['export_file_type'].'&final_export_array=1';
  }


}


########## Processing data above ############
########## Displaying data below ############

// $cid should come in from url
$result = db_query("SELECT * FROM ".$prefix."categories WHERE cid='".$_GET['cid']."'");
list($cid1,$category_name1,$parent_id1,$category_description1,$owner_id1) = db_fetch_array($result);
if ($category_description1 != "") { $category_description1 = " - ".$category_description1; }

echo '

<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="90%" border="1" cellspacing="2" cellpadding="3" align="center">
  <tr valign="top">
    <td class="browsetitle" colspan="3" valign="top" align="center">Export by Category</td>
  </tr>
  <tr>
    <td align="left" class="categorynav">
     ';

function trace_categories($parent_id) {
        GLOBAL $built_menu,$prefix;
        // Recursive function to display parent categories
        $result = db_query("SELECT cid,category_name,parent_id FROM ".$prefix."categories WHERE cid='$parent_id'");
        while ($r = db_fetch_array($result)) {
                $cid = $r["cid"];
                $category_name = $r["category_name"];
                $parent_id = $r["parent_id"];
                    if ($parent_id > 0) { // meaning it actually has a parent category
                      $built_menu = " &gt;&gt; <a href=\"export.php?cid=".$cid."\">".$category_name."</a>".$built_menu;
                      $again = trace_categories($parent_id); // run it again
                    } else {
                      $built_menu = "<a href=\"export.php?cid=".$cid."\">".$category_name."</a>".$built_menu;
                    }
        }

return $built_menu;
}


$build_menu = trace_categories($parent_id1);

if ($build_menu) {
  $build_menu = "<a href=\"export.php?cid=0\">SELECT CATEGORIES</a> &gt;&gt; ".$build_menu." &gt;&gt; <a href=\"export.php?cid=".$cid1."\">".$category_name1."</a>";
} elseif ($category_name1) {
  $build_menu = "<a href=\"export.php?cid=0\">SELECT CATEGORIES</a> &gt;&gt; <a href=\"export.php?cid=".$cid1."\">".$category_name1."</a>";
} else {
  $build_menu = "<a href=\"export.php?cid=0\">SELECT CATEGORIES</a>";
}

echo $build_menu;


echo '</td>
      <td width="300" align="center" class="categorynav">
        <font size="4"><b>Current Export Selections</b></font>
      </td>

    </tr>
    <tr valign="top">
      <td valign="top" align="left"><br>
      <blockquote><b><u>Use the key below to add categories to the export list:</u></b><br><br>
      <a class="export_legend1" href="">&nbsp;R&nbsp;</a> = Recursively select this category and all subcategories and snippets below it.<br>
      <a class="export_legend2" href="">&nbsp;S&nbsp;</a> = Select just this category, excluding any subcategories or snippets.<br>
      </blockquote><hr>
     ';

if (!$_GET['cid']) {
  echo '<blockquote><b>Use the links below to browse to a desired category.</b></blockquote>';
}


// Build parent categories menu above selected category
$cnt = 0;
$parent_id2 = $parent_id1;
while ($parent_id2 > 0) {
  $result = db_query("SELECT * FROM ".$prefix."categories WHERE cid='$parent_id2'");
  list($cid2,$category_name2,$parent_id2,$category_description2) = db_fetch_array($result);
  if (!$cnt) { $redirect_from_del = $cid2; }

    // Count snippets under category
    $result_snippets = db_query("SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid2'");
    $num_rows_snippets2 = db_num_rows($result_snippets);
    if ($num_rows_snippets2) { $num_snippets2 = "  ($num_rows_snippets2 Snippets)"; }

  if ($category_description2 != "") { $category_description2 = " - ".$category_description2; }
  $parent_categories = "<ul><li><a href=\"export.php?cid=".$cid2."\">".$category_name2."</a>".$category_description2." <b>$num_snippets2</b></li>".$parent_categories;
  unset($num_rows_snippets2,$num_snippets2);
  $cnt++;
}

echo $parent_categories;

// Count snippets under category
$result_snippets = db_query("SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid1'");
$num_rows_snippets1 = db_num_rows($result_snippets);
if ($num_rows_snippets1) { $num_snippets1 = "  ($num_rows_snippets1 Snippets)"; }

if ($_GET['cid'] > 0) {
  print "<ul><li><a class=\"export_legend1\" href=\"export.php?cid=".$_GET['cid']."&export_category=".$cid1."&type=recursive\">&nbsp;R&nbsp;</a>
         <a class=\"export_legend2\" href=\"export.php?cid=".$_GET['cid']."&export_category=".$cid1."&type=single\">&nbsp;S&nbsp;</a>
	 <a href=\"export.php?cid=".$cid1."\"><span style='background-color:yellow'>".$category_name1."</span>
	 </a>".$category_description1." <b>$num_snippets1</b></li>";
}

// Build categories menu below selected category
print "<ul>";

$result = db_query("SELECT * FROM ".$prefix."categories WHERE parent_id='".$_GET['cid']."'");
while ($r = db_fetch_array($result)) {
        $cid3 = $r["cid"];
        $category_name3 = $r["category_name"];
        $parent_id3 = $r["parent_id"];
        $category_description3 = $r["description"];
        if ($category_description3 != "") {
          $category_description3 = " - ".$category_description3;
        }

        // Count snippets under category
        $result_snippets = db_query("SELECT sid FROM ".$prefix."snippets WHERE category_id='$cid3'");
        $num_rows_snippets3 = db_num_rows($result_snippets);

	// Count subcategories under category
	if ($_GET['cid'] > 0) {
          $result_categories = db_query("SELECT cid FROM ".$prefix."categories WHERE parent_id='$cid3'");
          $num_rows_categories3 = db_num_rows($result_categories);
	}

	if ($num_rows_snippets3 && $num_rows_categories3) {
	    $summary = "  ($num_rows_snippets3 Snippets, $num_rows_categories3 Subcategories)";
	} else if (($num_rows_snippets3) && (!$num_rows_categories3)) {
	    $summary = "  ($num_rows_snippets3 Snippets)";
	} else if (($num_rows_categories3) && (!$num_rows_snippets3)) {
	    $summary = "  ($num_rows_categories3 Subcategories)";
	}


	print "<li><a href=\"export.php?cid=".$cid3."\">".$category_name3."</a>".$category_description3." <b>$summary</b></li>";
        unset($num_rows_snippets3,$num_snippets3,$num_categories3,$summary);
}

print "</ul>";
print "</ul>";

for ($i=0; $i<$cnt; $i++) {
  print "</ul>";
}


echo '
    <br></td>
    <td width="300" align="center" valign="top" class="categorynav">
      <table width="90%" align="center" cellpadding="3">
        <tr>
	  <td align="left" valign="top"><br>
            <ul>
	      <li>Total Snippets: '.sizeof($_SESSION['export_snippets']).'</li>
	      <li>Total Categories: '.sizeof($_SESSION['export_categories']).'</li>
            </ul>
	    <br>
            <center><a href="export.php?cid='.$_GET['cid'].'&clear_exports=1">Clear Export List</a><br><br>
	    <form action="'.$_SERVER['PHP_SELF'].'?cid='.strip_tags($_GET['cid']).'" method="POST">
	    Select export file format:<br>
              <select name="export_file_type" size="1">
			     <option value="csv">Comma Separated Value (CSV)</option>
			  </select>
              <br><br>
              <input type="hidden" name="confirm_export" value="1">
	      <input type="hidden" name="cid" value="'.strip_tags($_GET['cid']).'">
              <input type="submit" name="submit" value="CREATE EXPORT FILE">
	    </form>
	    <br><br>
     ';

if ($final_export_array) {
  echo '<a href="export.php'.$final_export_array.'" class="export_legend3">&nbsp;>>&nbsp;Click Here to Download Export File&nbsp;<<&nbsp;</a><p>&nbsp;</p>';
}

echo '
	    </center>
	  </td>
        </tr>
      </table>
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
      <blockquote><a class="export_legend3" href="">&nbsp;S&nbsp;</a> = Add this snippet to the export list.<br></blockquote><hr>
      <ul>
     ';

$result = db_query("SELECT * FROM ".$prefix."snippets WHERE category_id='$cid1' ORDER BY name ASC");
while ($r = db_fetch_array($result)) {
       $sid = $r["sid"];
       $snippet_name = $r["name"];
       $snippet_description = $r["description"];
       if ($snippet_description != "") { $snippet_description = " - ".$snippet_description; }

       print "<li><a class=\"export_legend3\" href=\"export.php?cid=".$_GET['cid']."&export_snippet=".$sid."\">&nbsp;S&nbsp;</a>&nbsp;
              <a href=\"snippet.php?sid=".$sid."\">".$snippet_name."</a>".$snippet_description."</li>";

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
