<?php

include_once("include/header.php");

if ($_SESSION['isadmin'] != $glbl_hash) {
  print '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=index.php">';
  exit; // Redirect browser and skip the rest
}

// User must be authenticated (above), so we can move on


echo '

<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="1" cellspacing="2" cellpadding="3" align="center">
  <tr valign="top">
    <td class="indextitle" valign="top" align="center">phpCodeCabinet Administration Menu</td>
  </tr>
  <tr valign="top">
    <td valign="top" align="center"><a href="category.php">Add/Modify Categories</a></td>
  </tr>
  <tr valign="top">
    <td valign="top" align="center"><a href="addedit-users.php">Add/Modify Users</a></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>

     ';


include_once("include/footer.php");

?>
