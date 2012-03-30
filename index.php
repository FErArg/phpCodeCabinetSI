<?php

include_once("header.php");


echo '

<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="640" border="1" cellspacing="4" cellpadding="3" align="center">
  <tr valign="top">
    <td class="indextitle" colspan="3" valign="top" align="center">phpCodeCabinetSI Men&uacute; Principal</td>
  </tr>
  <tr valign="top">
    <td class="indexmenu" colspan="2" valign="middle" align="center"><a href="browse.php">Ver c&oacute;digo por Categor&iacute;a</a></td>
    <td class="indextitle"  rowspan="2" valign="top" align="center">
     ';

  $searchbox_type = "vertical";
  include("searchbox.php");

echo '
    </td>

  </tr>
  <tr valign="top">
    <td class="indexmenu" width="200" valign="middle" align="center">
     ';

    if ($_SESSION['isloggedin'] == $glbl_hash) {
      echo '<a href="user.php?op=logout">LOGOUT</a>';
    } else {
      echo '<a href="user.php">LOGIN</a>';
    }


echo '
    </td>
    <td class="indexmenu" width="200" valign="middle" align="center"><a href="input.php">Agregar C&oacute;digo</a></td>
    </td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>

     ';


include_once("footer.php");

?>
