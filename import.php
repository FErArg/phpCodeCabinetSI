<?php

include_once("include/header.php");

if ($_SESSION['isloggedin'] != $glbl_hash) {
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=user.php">';
  exit; // Redirect browser and skip the rest
}

// SerInformaticos
foreach( $_POST as $key => $value ){
	$_POST[$key] = filter_var($_POST[$key], FILTER_SANITIZE_STRING);
}


// User must be authenticated (above), so we can move on

function insert_snips($old_cid, $new_cid, $each_snippet_record, $bypass) {

    global $post_snip_array,$prefix,$allowed_html_tags;

    $sizeof_each_snippet_record = sizeof($each_snippet_record);

    for($y=0; $y < $sizeof_each_snippet_record; $y++) {

	  if ((($old_cid == $each_snippet_record[$y][8]) || ($bypass == 1)) && (in_array($each_snippet_record[$y][0], $post_snip_array))) {

         $snip_name = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][1]);
         $snip_desc = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][2]);
         $snip_comment = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][3]);
         $snip_author_name = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][4]);
         $snip_language = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][6]);
         $snip_snippet = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$y][11]);

         $snip_name = strip_tags(addslashes($snip_name), $allowed_html_tags);
         $snip_desc = strip_tags(addslashes($snip_desc), $allowed_html_tags);
         $snip_comment = strip_tags(addslashes($snip_comment), $allowed_html_tags);
         $snip_author_name = strip_tags(addslashes($snip_author_name), $allowed_html_tags);
         $snip_language = strip_tags(addslashes($snip_language), $allowed_html_tags);
         $snip_snippet = addslashes($snip_snippet);

	     $check_for_duplicates = db_query("SELECT sid FROM ".$prefix."snippets WHERE name='".$snip_name."' AND description='".$snip_desc."' AND comment='".$snip_comment."' AND author_name='".$snip_author_name."' AND author_email='".$each_snippet_record[$y][5]."' AND language='".$snip_language."' AND highlight_mode='".$each_snippet_record[$y][7]."' AND category_id='".$new_cid."'");
	     list($duplicate_sid) = db_fetch_array($check_for_duplicates);

	     if (!$duplicate_sid) {

                 $insert = db_query("INSERT INTO ".$prefix."snippets (name, description, comment, author_name, author_email, language, highlight_mode, category_id, last_modified, owner_id, snippet) VALUES ('".$snip_name."','".$snip_desc."','".$snip_comment."','".$snip_author_name."','".$each_snippet_record[$y][5]."','".$snip_language."','".$each_snippet_record[$y][7]."','".$new_cid."','".$each_snippet_record[$y][9]."','".$_SESSION['userid']."','".$snip_snippet."')");
				 if ($insert == 1) {
				   $each_snippet_record[$y][99] = 1;
				 }

         } else if (($duplicate_sid) && ($_POST['overwrite'] == 1)) {
	     // The only difference between this condition and the one above is that 'sid' is being replaced, rather than autoincremented.

				 $update = db_query("UPDATE ".$prefix."snippets SET name='".$snip_name."',description='".$snip_desc."',comment='".$snip_comment."',author_name='".$snip_author_name."',author_email='".$each_snippet_record[$y][5]."',language='".$snip_language."',highlight_mode='".$each_snippet_record[$y][7]."',category_id='".$new_cid."',last_modified='".$each_snippet_record[$y][9]."',owner_id='".$_SESSION['userid']."',snippet='".$snip_snippet."' WHERE sid='".$duplicate_sid."'");
				 if ($update == 1) {
                   $each_snippet_record[$y][99] = 1;
				 }

	     } else if (($duplicate_sid) && ($_POST['overwrite'] != 1)) {

	             $import_error .= "ERROR: Duplicate snippet found.  Import failed.&nbsp;&nbsp;|&nbsp;&nbsp;<b>Snippet name: $snip_name</b><br><br>";
		         $each_snippet_record[$y][99] = 2;  // Set bit to notify user that import failed due to lack of overwrite permissions.

	     }

		 // now remove sid from the post_snip_array to avoid re-importing the snippet
		 $key_to_remove = array_search($each_snippet_record[$y][0], $post_snip_array);
		 unset($post_snip_array[$key_to_remove]);


      }

    }

return $import_error;
}



function insert_subs($cid, $parent_id, $each_category_record, $each_snippet_record) {

  global $prefix,$post_cat_array;

  $sizeof_each_category_record = sizeof($each_category_record);

  for($x=0; $x < $sizeof_each_category_record; $x++) {

      if (($cid == $each_category_record[$x][0]) && (in_array($each_category_record[$x][0], $post_cat_array))) {

          $cat_name = ereg_replace("\[phpcc-comma\]",",",$each_category_record[$x][1]);
          $cat_desc = ereg_replace("\[phpcc-comma\]",",",$each_category_record[$x][3]);

	  $check_for_duplicates = db_query("SELECT cid FROM ".$prefix."categories WHERE category_name='".$cat_name."' AND parent_id='".$parent_id."' AND description='".$cat_desc."'");
	  list($duplicate_cid) = db_fetch_array($check_for_duplicates);

	  if (!$duplicate_cid) {

	      $insert = db_query("INSERT INTO ".$prefix."categories (category_name, parent_id, description, owner_id) VALUES ('".$cat_name."','".$parent_id."','".$cat_desc."','".$_SESSION['userid']."')");
		  $key_to_remove = array_search($each_category_record[$x][0], $post_cat_array);
		  unset($post_cat_array[$key_to_remove]);
	      $each_category_record[$x][99] = 1;
	      $query = db_query("SELECT cid FROM ".$prefix."categories WHERE category_name='".$cat_name."' AND parent_id='".$parent_id."' AND description='".$cat_desc."' AND owner_id='".$_SESSION['userid']."'");
	      list($new_cid) = db_fetch_array($query);

              $get_snips = insert_snips($cid, $new_cid, &$each_snippet_record, 0);
	      if ($get_snips) { $import_error .= $get_snips; }  // If $get_snips has a value, we must have an import error.

	      for($z=0; $z < $sizeof_each_category_record; $z++) {

	          if (($cid == $each_category_record[$z][2]) && (in_array($each_category_record[$z][0], $post_cat_array))) {

                      $recurse = insert_subs($each_category_record[$z][0], $new_cid, &$each_category_record, &$each_snippet_record);
		      if ($recurse) { $import_error .= $recurse; }  // If $recurse has a value, we must have an import error.

	          }

	      }

	  } else if (($duplicate_cid) && ($_POST['overwrite'] == 1)) {
	      // The only difference between this condition and the one above is that 'cid' is being replaced, rather than autoincremented.

	      $insert = db_query("INSERT INTO ".$prefix."categories (cid, category_name, parent_id, description, owner_id) VALUES ('".$duplicate_cid."','".$cat_name."','".$parent_id."','".$cat_desc."','".$_SESSION['userid']."')");
		  $key_to_remove = array_search($each_category_record[$x][0], $post_cat_array);
		  unset($post_cat_array[$key_to_remove]);
	      $each_category_record[$x][99] = 1;

	      $new_cid = $duplicate_cid; // No need to query for cid -- we already know it.

              $get_snips = insert_snips($cid, $new_cid, &$each_snippet_record, 0);
	      if ($get_snips) { $import_error .= $get_snips; }  // If $get_snips has a value, we must have an import error.

	      for($z=0; $z < $sizeof_each_category_record; $z++) {

	          if (($cid == $each_category_record[$z][2]) && (in_array($each_category_record[$z][0], $post_cat_array))) {

                      $recurse = insert_subs($each_category_record[$z][0], $new_cid, &$each_category_record, &$each_snippet_record);
		      if ($recurse) { $import_error .= $recurse; }  // If $recurse has a value, we must have an import error.

	          }

	      }

	  } else if (($duplicate_cid) && ($_POST['overwrite'] != 1)) {
	      // A duplicate category exists, and we are not allowed to overwrite, so pass the 'cid' of the existing category, and continue the recursion.

	      $import_error .= "ERROR: Duplicate category found.  Import failed.&nbsp;&nbsp;|&nbsp;&nbsp;<b>Category name: $cat_name</b><br><br>";
	      $each_category_record[$x][99] = 2;  // Set bit to notify user that import failed due to lack of overwrite permissions.

		  $key_to_remove = array_search($each_category_record[$x][0], $post_cat_array);
		  unset($post_cat_array[$key_to_remove]);

	      $new_cid = $duplicate_cid; // No need to query for cid -- we already know it.

              $get_snips = insert_snips($cid, $new_cid, &$each_snippet_record, 0);
	      if ($get_snips) { $import_error .= $get_snips; }  // If $get_snips has a value, we must have an import error.

	      for($z=0; $z < $sizeof_each_category_record; $z++) {

	          if (($cid == $each_category_record[$z][2]) && (in_array($each_category_record[$z][0], $post_cat_array))) {

                      $recurse = insert_subs($each_category_record[$z][0], $new_cid, &$each_category_record, &$each_snippet_record);
		      if ($recurse) { $import_error .= $recurse; }  // If $recurse has a value, we must have an import error.

	          }

	      }

	  }

      }

  }

return $import_error;
}



if (($_POST['categories'] || $_POST['snippet']) && ($_POST['select_imports'])) {
// Step 2 has been submitted, and we are now ready to import the selections to the database.
// After that, we will pass the user back to Step 2 to review the selections.
// Incoming categories[] array will just be the CID, snippet[] will be the SID.

  $each_category_record = unserialize(base64_decode($_POST['ser_cats']));
  $sizeof_each_category_record = sizeof($each_category_record);
  $each_snippet_record = unserialize(base64_decode($_POST['ser_snips']));
  $sizeof_each_snippet_record = sizeof($each_snippet_record);

  $post_cat_array = &$_POST['categories'];
  $sizeof_post_cat_array = sizeof($post_cat_array);
  $post_snip_array = &$_POST['snippet'];


  for ($x=0; $x < $sizeof_post_cat_array; $x++) {
  // Step through the categories[] array from form

      for ($y=0; $y < $sizeof_each_category_record; $y++) {
      // Step through the each_category_record[] array looking for a matching CID.

          if ($post_cat_array[$x] == $each_category_record[$y][0]) {
	  // If a match is found, enter it into the database and flag it so we know it has already been imported.

	    $populate_db = insert_subs($post_cat_array[$x], $_POST['import_to_parent'], &$each_category_record, &$each_snippet_record);
	    if ($populate_db) { $import_error .= $populate_db; } // If $populate_db has a value, there must have been an error.

	  }

      }

  }


  if ($sizeof_each_snippet_record >= 1) {
  // Check for leftover, uncategorized snippets to put in the $import_to_parent category.

      $populate_db = insert_snips(0, $_POST['import_to_parent'], &$each_snippet_record, 1);
      if ($populate_db) { $import_error .= $populate_db; } // If $populate_db has a value, there must have been an error.

  }

  if (($import_error) && ($_POST["verbose"] == 1)) {

      echo "<p>&nbsp;</p>
            $import_error
	    <p>&nbsp;</p>
           ";

  }

  $back_to_step2 = 1;

}



if ((($_POST['getfile'] == 1) && ($_FILES['upfile']['name'] != "")) || (($back_to_step2) && (($each_category_record) || ($each_snippet_record)))) {
// The file has been uploaded from Step 1, or is being passed back from the database import,
// so we must now parse the data and present it to the user or re-display the data respectively.

  if (!$back_to_step2) {
    // Assume this is the first time we've parsed the data from the file itself,
    // otherwise we'd already have either $each_category_record or $each_snippet_record.

    $sizeof_file = $_FILES['upfile']['size'];
    $handle = fopen($_FILES['upfile']['tmp_name'], "r");
    $file_raw_contents = fread($handle, $sizeof_file);
    fclose($handle);

    ######### CSV File Format for Import #########

    $raw_split_array = explode("-------0|0-------", $file_raw_contents);

    $raw_categories = $raw_split_array[0];
    $raw_snippets = $raw_split_array[1];

    $raw_category_array = explode(",", $raw_categories);
    $raw_snippet_array = explode(",", $raw_snippets);

    ##############################################

    // Parse the raw category array to define fields for each category to be imported.
    $sizeof_raw_category_array = sizeof($raw_category_array);
    $record_counter = 0; // Record counter will be used to number each category being imported.
    $field_counter = 0; // Field counter will be used to number the fields for each category being imported (cid, category_name, etc.)
    for($x=0; $x < $sizeof_raw_category_array; $x++) {

      $each_category_record[$record_counter][$field_counter] = $raw_category_array[$x];
      if ($field_counter == 4) { // If 4, we've received all fields for this category, so it's time to move to the next record
        $record_counter++;
        $field_counter = 0;
      } else {
        $field_counter++;
      }

    }

  } // end if $back_to_step2

  $serialized_category_record = base64_encode(serialize($each_category_record));


  if (!$back_to_step2) {
    // Assume this is the first time we've parsed the data from the file itself,
    // otherwise we'd already have either $each_category_record or $each_snippet_record.

    // Parse the raw snippet array to define fields for each snippet to be imported.
    $sizeof_raw_snippet_array = sizeof($raw_snippet_array);
    $record_counter = 0; // Record counter will be used to number each snippet being imported.
    $field_counter = 0; // Field counter will be used to number the fields for each snippet being imported (sid, description, etc.)
    for($x=0; $x < $sizeof_raw_snippet_array; $x++) {

      $each_snippet_record[$record_counter][$field_counter] = $raw_snippet_array[$x];
      if ($field_counter == 11) { // If 11, we've received all fields for this category, so it's time to move to the next record
		$record_counter++;
        $field_counter = 0;
      } else {
        $field_counter++;
      }

    }

  } // end if $back_to_step2


  $serialized_snippet_record = chunk_split(base64_encode(serialize($each_snippet_record)));



/*

For Reference:

$each_category_record[n][0] - cid
$each_category_record[n][1] - category_name
$each_category_record[n][2] - parent_id
$each_category_record[n][3] - description
$each_category_record[n][4] - owner_id
$each_category_record[n][99] - bit to tell if category has already been imported

$each_snippet_record[n][0] - sid
$each_snippet_record[n][1] - name
$each_snippet_record[n][2] - description
$each_snippet_record[n][3] - comment
$each_snippet_record[n][4] - author_name
$each_snippet_record[n][5] - author_email
$each_snippet_record[n][6] - language
$each_snippet_record[n][7] - highlight_mode
$each_snippet_record[n][8] - category_id
$each_snippet_record[n][9] - last_modified
$each_snippet_record[n][10] - owner_id
$each_snippet_record[n][11] - snippet
$each_snippet_record[n][99] - bit to tell if snippet has already been imported


*/



function get_subs($cid, $indice) {

  global $each_category_record,$each_snippet_record;

  $cat_name = ereg_replace("\[phpcc-comma\]",",",$each_category_record[$indice][1]);
  $cat_desc = ereg_replace("\[phpcc-comma\]",",",$each_category_record[$indice][3]);
  if (!$cat_desc) { $cat_desc = "<i>No description</i>"; }

  if ($each_category_record[$indice][99] == 2) {  // Has category already been imported?
    echo '<ul><li class="import_legend4"><input type="checkbox" name="categories[]" value="'.rtrim(trim($each_category_record[$indice][0])).'">'.$cat_name.' - '.$cat_desc.'</li>';
  } else if ($each_category_record[$indice][99] == 1) {
    echo '<ul><li class="import_legend3"><input type="checkbox" name="categories[]" value="'.rtrim(trim($each_category_record[$indice][0])).'">'.$cat_name.' - '.$cat_desc.'</li>';
  } else {
    echo '<ul><li class="import_legend1"><input type="checkbox" name="categories[]" value="'.rtrim(trim($each_category_record[$indice][0])).'">'.$cat_name.' - '.$cat_desc.'</li>';
  }

  $sizeof_each_snippet_record = sizeof($each_snippet_record);
  for($x=0; $x<$sizeof_each_snippet_record; $x++) {
    if ($each_snippet_record[$x][8] == $cid) {
      $snip_name = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$x][1]);
      $snip_desc = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$x][2]);
      if (!$snip_desc) { $snip_desc = "<i>No description</i>"; }
      if ($each_snippet_record[$x][99] == 2) {  // Has snippet already been imported?
        echo '<ul><li class="import_legend4"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
      } else if ($each_snippet_record[$x][99] == 1) {
        echo '<ul><li class="import_legend3"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
      } else {
        echo '<ul><li class="import_legend2"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
      }
    }
  }



  $sizeof_each_category_record = sizeof($each_category_record);

  for($x=0; $x<$sizeof_each_category_record; $x++) {

    if ($each_category_record[$x][2] == $cid) {
      $recurse = get_subs($each_category_record[$x][0], $x);
    }

  }

  echo '</ul>';

}


//File has been uploaded, now present the import selection form

echo '
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="90%" border="1" cellspacing="2" cellpadding="5" align="center">
  <tr valign="top">
    <td class="browsetitle" colspan="2" valign="top" align="center">Import a Category or Snippet</td>
  </tr>
  <tr>
    <td valign="top">
    <p>&nbsp;</p>
    <blockquote>
    <h3>Step 2: Choose Selections for Import</h3>
    <b><u>Use the key below to identify categories and snippets from the import list:</u></b><br>
    <ul>
      <li class="import_legend1">&nbsp;CATEGORY&nbsp; = Category available for import.</li>
      <li class="import_legend2">&nbsp;SNIPPET&nbsp; = Snippet available for import.</li>
      <li class="import_legend3">&nbsp;ALREADY IMPORTED&nbsp; = Category or snippet previously imported.</li>
      <li class="import_legend4">&nbsp;FAILED IMPORT&nbsp; = Failed previous import attempt.</li>
    </ul>
    </blockquote><hr>

    <form name="hier" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" method="post">';


     $sizeof_each_category_record = sizeof($each_category_record);

     for($x=0; $x<$sizeof_each_category_record; $x++) {
       // Create an array of cids to check parent_id against
       $temp_cids_array[$x] = $each_category_record[$x][0];
     }

     for($x=($sizeof_each_category_record)-1; $x>=0; $x--) {

       // Check to see if the $cid has a parent included in the import.
       // If so, it will be picked up within the function, so it does not need to be sent to function.
       if ((!in_array($each_category_record[$x][2], $temp_cids_array)) && ($each_category_record[$x][2] != "")) {
         $output = get_subs($each_category_record[$x][0], $x);
       }

     }



     // Search snippets and display any remaining snippets that were not imported with a category.
     $sizeof_each_snippet_record = sizeof($each_snippet_record);

     for($x=0; $x<$sizeof_each_snippet_record; $x++) {

       if (!in_array($each_snippet_record[$x][8], $temp_cids_array)) {
         // The snippet's original category_id was not included in the import file,
         // so display the snippet by itself.

         $snip_name = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$x][1]);
         $snip_desc = ereg_replace("\[phpcc-comma\]",",",$each_snippet_record[$x][2]);

         if (!$snip_desc) { $snip_desc = "<i>No description</i>"; }

         if ($each_snippet_record[$x][99] == 2) {  // Has snippet already been imported?
           echo '<ul><li class="import_legend4"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
         } else if ($each_snippet_record[$x][99] == 1) {
           echo '<ul><li class="import_legend3"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
	     } else {
	       echo '<ul><li class="import_legend2"><input type="checkbox" name="snippet[]" value="'.rtrim(trim($each_snippet_record[$x][0])).'">'.$snip_name.' - '.$snip_desc.'</li></ul>';
         }
       }

	   if ($each_snippet_record[$x][99] == 2) { unset($each_snippet_record[$x][99]); }

     }




echo '
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </td>
    <td valign="top" width="200">
    <p>&nbsp;</p>

    <script language="JavaScript">

    function checkCats() {
      with (document.hier) {
        for (var i=0; i < elements.length; i++) {
            if (elements[i].type == \'checkbox\' && elements[i].name == \'categories[]\')
            elements[i].checked = true;
        }
      }
    }

    function checkSnips() {
      with (document.hier) {
        for (var i=0; i < elements.length; i++) {
            if (elements[i].type == \'checkbox\' && elements[i].name == \'snippet[]\')
            elements[i].checked = true;
        }
      }
    }

    function checkAll() {
      with (document.hier) {
        for (var i=0; i < elements.length; i++) {
            if (elements[i].type == \'checkbox\' && elements[i].name != \'overwrite\' && elements[i].name != \'verbose\')
            elements[i].checked = true;
        }
      }
    }

    </script>

    <INPUT onclick=checkAll() type="button" value="Select Everything"><br><br>
    <INPUT onclick=checkCats() type="button" value="Select Only Categories"><br><br>
    <INPUT onclick=checkSnips() type="button" value="Select Only Snippets"><br><br>
    <INPUT type=reset value="Uncheck All"><br><br>

    IMPORT INTO CATEGORY:<br><select name="import_to_parent" size="1">

    <option value="0" selected>TOP LEVEL</option>';

    $query = "SELECT cid,category_name FROM ".$prefix."categories ORDER BY category_name ASC";
    $result = db_query($query);
    while ($r = db_fetch_array($result)) {
           $select_cid = $r["cid"];
           $select_category_name = $r["category_name"];
           print "<option value=\"$select_cid\">$select_category_name</option>";
    }

echo '
    </select><br><br>
    <INPUT type="checkbox" name="overwrite" value="1"> <font size="2">OVERWRITE DUPLICATES</font><br>
	<INPUT type="checkbox" name="verbose" value="1"> <font size="2">VERBOSE ERRORS</font><br><br>
      <input type="hidden" name="ser_cats" value="'.$serialized_category_record.'">
      <input type="hidden" name="ser_snips" value="'.$serialized_snippet_record.'">
      <input type="hidden" name="select_imports" value="1"><hr><br>
      <input type="submit" value="PROCEED WITH IMPORT" name="submit">
      </form>
      <p>&nbsp;</p>
    </td>
  </tr>
</table>
<p>&nbsp;</p>
     ';



} else if (!$_POST['select_imports']) {

// Present file upload form
echo '

<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="90%" border="1" cellspacing="2" cellpadding="3" align="center">
  <tr valign="top">
    <td class="browsetitle" valign="top" align="center">Import a Category or Snippet</td>
  </tr>
  <tr>
    <td valign="top" align="center">
      <p>&nbsp;</p>
      <h3>Step 1: File Upload</h3>
      <form action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" method="post">

      Select File to Upload: <input type="file" name="upfile">&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="hidden" name="getfile" value="1">
      <input type="submit" value="Upload File" name="submit">

      </form>
      <p>&nbsp;</p>
    </td>
  </tr>
</table>

     ';

}




include_once("include/footer.php");

?>
