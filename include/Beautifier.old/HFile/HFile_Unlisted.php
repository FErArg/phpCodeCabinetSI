<?php
global $BEAUT_PATH;
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_Unlisted extends HFile{
   function HFile_Unlisted(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Unlisted Languages (no highlighting)
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->blockcommenton    	= array();
$this->blockcommentoff   	= array();

// Keywords (keyword mapping to colour number)

$this->keywords          = array();

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.



$this->linkscripts    	= array(
			"1" => "donothing",
			"2" => "donothing",
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
