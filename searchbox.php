<?php

if ($searchbox_type == "vertical") {
echo '
    <form action="search.php" method="POST">
    <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
      <tr>
        <td align="center" valign="top">
        <h3>Buscar:</h3>
        <input type="text" name="querywords" size="25"><br>
        Requiere: <select name="querytype" size="1">
          <option value="allwords" selected>Todas palabras</option>
          <option value="anywords">Algunas palabras</option>
          <option value="exactphrase">Frase Exacta</option>
        </select><br>
        N&uacute;mero de Resultados: <select name="num_results" size="1">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select><br><br>
        <input type="hidden" name="searchtype" value="basic">
        <center><input type="submit" name="submit" value="BUSCAR"></center>
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
        <font size="4">Buscar:</font> <input type="text" name="querywords" size="25">&nbsp;
        <input type="hidden" name="searchtype" value="basic">
        <input type="submit" name="submit" value="BUSCAR"><br><br>
        Require: <select name="querytype" size="1">
          <option value="allwords" selected>Todas palabras</option>
          <option value="anywords">Algunas palabras</option>
          <option value="exactphrase">Frase Exacta</option>
        </select>&nbsp;&nbsp;
        uacute;mero de Resultados: <select name="num_results" size="1">
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
