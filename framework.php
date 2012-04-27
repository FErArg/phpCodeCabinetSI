<?php
$version = "0.6.3";

function limpiarTexto1($texto){
	$texto = str_replace('Ñ', '&Ntilde;', $texto);
	$texto = str_replace('ñ', '&ntilde;', $texto);
	$texto = str_replace('Ü', '&Uuml;', $texto);
	$texto = str_replace('ü', '&uuml;', $texto);
	$texto = str_replace('€', '&euro;', $texto);
	$texto = str_replace('>', '&gt;', $texto);
	$texto = str_replace('<', '&lt;', $texto);
	$texto = str_replace('/', '&#47;', $texto);
	$texto = str_replace('¡', '&iexcl;', $texto);
	$texto = str_replace('¿', '&iquest;', $texto);
	$texto = str_replace('á', '&aacute;', $texto);
	$texto = str_replace('é', '&eacute;', $texto);
	$texto = str_replace('í', '&iacute;', $texto);
	$texto = str_replace('ó', '&oacute;', $texto);
	$texto = str_replace('ú', '&uacute;', $texto);
	$texto = str_replace('Á', '&aacute;', $texto);
	$texto = str_replace('É', '&eacute;', $texto);
	$texto = str_replace('Í', '&iacute;', $texto);
	$texto = str_replace('Ó', '&oacute;', $texto);
	$texto = str_replace('Ú', '&uacute;', $texto);
	$texto = str_replace('–', '-', $texto);
	$texto = str_replace('"', '&quot;', $texto);
	$texto = str_replace("'", '&apos;', $texto);
	$texto = str_replace('“', '&rdquo;', $texto);
	$texto = str_replace('”', '&ldquo;', $texto);
	$texto = str_replace('«', '&laquo;', $texto);
	$texto = str_replace('»', '&raquo;', $texto);
	$texto = str_replace('‘', '&lsquo;', $texto);
	$texto = str_replace('’', '&rsquo;', $texto);
	return $texto;
}
?>
