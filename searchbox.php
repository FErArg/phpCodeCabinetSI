<?php

if ($searchbox_type == "vertical") {
echo '
    <form action="search.php" method="POST">
    <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td align="center" valign="top">
        <h3>SEARCH:</h3>
        <input type="text" name="querywords" size="25"><br>
        Require: <select name="querytype" size="1">
          <option value="allwords" selected>All Words</option>
          <option value="anywords">Any Words</option>
          <option value="exactphrase">Exact Phrase</option>
        </select><br>
        Number of Results: <select name="num_results" size="1">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select><br><br>
        <input type="hidden" name="searchtype" value="basic">
        <center><input type="submit" name="submit" value="SEARCH"></center>
        <br>
        </td>
      </tr>
    </table>
    </form>
     ';
}

if ($searchbox_type == "horizontal") {
echo '
    <form action="search.php" method="POST">
    <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td align="center" valign="top">
        <font size="4">SEARCH:</font> <input type="text" name="querywords" size="25">&nbsp;
        <input type="hidden" name="searchtype" value="basic">
        <input type="submit" name="submit" value="SEARCH"><br><br>
        Require: <select name="querytype" size="1">
          <option value="allwords" selected>All Words</option>
          <option value="anywords">Any Words</option>
          <option value="exactphrase">Exact Phrase</option>
        </select>&nbsp;&nbsp;
        Number of Results: <select name="num_results" size="1">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
        </td>
      </tr>
    </table>
    </form>
     ';
}



?>
