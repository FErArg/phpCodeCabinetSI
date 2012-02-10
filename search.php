<?php

include_once("header.php");

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}

if ((!$_POST['submit']) && (!$_GET['num_results'])) {
  echo "<p>&nbsp;</p>";
  $searchbox_type = "horizontal";
  include("searchbox.php");
  echo "<p>&nbsp;</p>";
  include_once("footer.php");
  exit;
}

// $querywords variable to come in from url
  if ($_POST['querywords']) {
    $querywords_url = $_POST['querywords'];
  } else if ($_GET['querywords']) {
    $querywords_url = $_GET['querywords'];
  } else {
    $querywords_url = "nothingwasenteredinthesearchquery";
  }

// $querytype variable may come in from url
  if ($_POST['querytype']) {
    $querytype = $_POST['querytype'];
  } else {
    $querytype = $_GET['querytype'];
  }

  if ($querywords_url) {
    $querywords = explode(" ",$querywords_url);
    $sizeof_querywords = sizeof($querywords);

    if ($querytype == "allwords") {
    // 'All Words' was selected for search

	for ($i = 0; $i < $sizeof_querywords; $i++) {
          $where_statement_snippets .= "(name LIKE '%$querywords[$i]%' OR ";
          $where_statement_snippets .= "description LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "comment LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "author_name LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "author_email LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "language LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "snippet LIKE '%$querywords[$i]%')";
          if ($i < $sizeof_querywords - 1) { $where_statement_snippets .= " AND "; }
        }

    } // if allwords

    if ($querytype == "anywords") {
    // 'Any Words' was selected for search

        for ($i = 0; $i < $sizeof_querywords; $i++) {
          $where_statement_snippets .= "name LIKE '%$querywords[$i]%' OR ";
          $where_statement_snippets .= "description LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "comment LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "author_name LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "author_email LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "language LIKE '%$querywords[$i]%' OR ";
	  $where_statement_snippets .= "snippet LIKE '%$querywords[$i]%'";
          if ($i < $sizeof_querywords - 1) { $where_statement_snippets .= " OR "; }
        }

    } // if anywords

    if ($querytype == "exactphrase") {
    // 'Exact Phrase' was selected for search

        $querywords = implode(" ",$querywords);
        $where_statement_snippets .= "name LIKE '%$querywords%' OR ";
        $where_statement_snippets .= "description LIKE '%$querywords%' OR ";
	$where_statement_snippets .= "comment LIKE '%$querywords%' OR ";
	$where_statement_snippets .= "author_name LIKE '%$querywords%' OR ";
	$where_statement_snippets .= "author_email LIKE '%$querywords%' OR ";
	$where_statement_snippets .= "language LIKE '%$querywords%' OR ";
	$where_statement_snippets .= "snippet LIKE '%$querywords%'";

    } // if exactphrase



  } else {
    $querywords = "";
  }

  $redo_querywords = urlencode($querywords_url);

  $truncate_at = 150; // truncate size of description string

  if ($_POST['num_results']) {
    $limit = $_POST['num_results'];
  } else {
    $limit = $_GET['num_results'];
  }

  $numresults = db_query("SELECT sid FROM ".$prefix."snippets WHERE ".$where_statement_snippets." ORDER BY name DESC");
  $numrows = db_num_rows($numresults);

  echo "<p>&nbsp;</p>
        <table width='95%' border='0' cellspacing='0' cellpadding='5' align='center'>
          <tr>
            <td colspan='2'>Your search returned ".$numrows." result(s)<br><br></td>
          </tr>";

  // next determine if offset has been passed to script, if not use 0
  if ($_GET['offset']) {
      $offset = $_GET['offset'];
  } else {
      $offset = "0";
  }

  // get results
  if ($dbtype == "pgsql") {
      $result = db_query("SELECT sid,name,description,language,category_id,last_modified FROM ".$prefix."snippets WHERE ".$where_statement_snippets." ORDER BY name DESC limit ".$limit." offset ".$offset."");
  } else { // assume mysql
      $result = db_query("SELECT sid,name,description,language,category_id,last_modified FROM ".$prefix."snippets WHERE ".$where_statement_snippets." ORDER BY name DESC limit ".$offset.",".$limit."");
  }


  // now you can display the results returned

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

  $cnt = $offset;
  while ($r = db_fetch_array($result)) {
      $sid = $r["sid"];
      $name = $r["name"];
      $description = $r["description"];
      $cnt++;
      if ($cnt%2 == 0) {
        $search_class = "searchalt2";
      } else {
        $search_class = "searchalt1";
      }
      if (strlen($description) > ($truncate_at + 3)) {
        $description = substr($description,0,$truncate_at)."...";
      }
      $language = $r["language"];
      $last_modified = $r['last_modified'];
      $category_id = $r["category_id"];

      $query = db_query("SELECT category_name FROM ".$prefix."categories WHERE cid='".$category_id."'");
      list($category_name) = db_fetch_array($query);


  $build_menu = trace_categories($category_id);

  if ($build_menu) {
    $build_menu = "CATEGORY: ".$build_menu;
  } else {
    $build_menu = "CATEGORY: UNCATEGORIZED";
  }

      echo "<tr><td class=".$search_class." valign='top' align='left' width='10'>".$cnt.". </td>
            <td class=".$search_class."><a href='snippet.php?sid=".$sid."'>$name</a><br>
            $description<br>$build_menu<br>LANGUAGE: ".$language." &nbsp;&nbsp;&nbsp;  Last Modified: ".$last_modified."<br><br></td></tr>";
      unset($build_menu,$built_menu);
  }

  echo "</table>
        <p>&nbsp;</p>
        <center>";

  // next we need to do the links to other results

  if ($offset > 0) { // bypass PREV link if offset is 0
      $prevoffset = $offset - $limit;
      print "<a href=\"search.php?offset=$prevoffset&querywords=$redo_querywords&querytype=$querytype&num_results=$limit\">[PREV]</a> &nbsp; \n";
  }

  // calculate number of pages needing links
  $pages = intval($numrows/$limit);

  // $pages now contains int of pages needed unless there is a remainder from division
  if ($numrows%$limit) {
      // has remainder so add one page
      $pages++;
  }

  if ($pages > 1) {
      for ($i=1; $i<=$pages; $i++) { // loop thru
          $newoffset = ($limit * ($i-1));
          if ($newoffset == $offset) {
            print "<span style='background-color:yellow'><b>$i</b></span> &nbsp; \n";
          } else {
            print "<a href=\"search.php?offset=$newoffset&querywords=$redo_querywords&querytype=$querytype&num_results=$limit\">$i</a> &nbsp; \n";
          }
      }
  }


  // check to see if last page
  if($numrows-$offset > $limit){
      // not last page so give NEXT link
      $newoffset=$offset+$limit;
      print "<a href=\"search.php?offset=$newoffset&querywords=$redo_querywords&querytype=$querytype&num_results=$limit\">[NEXT]</a><p>\n";
  }

  echo "</center>";

  $searchbox_type = "horizontal";
  include("searchbox.php");

  echo "<p>&nbsp;</p>";


include_once("footer.php");

?>
