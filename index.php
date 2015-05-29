<?php

echo '<link type="text/css" href="rfc2html/table.css">';
echo '<script language="javascript" type="text/javascript" src="rfc2html/table.js"></script>';
echo ' <link rel="stylesheet" type="text/css" href="jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="jquery.dataTables.js"></script>';
/*

 <link rel="stylesheet" type="text/css" href="dataTables.jqueryui.css">
<script type="text/javascript" charset="utf8" src="dataTables.jqueryui.js" > </script>
';
 */
$rfile="/tmp/r.xml";
$rfile="rfc-index.xml";
/*
echo "<pre>";
$xml=simplexml_load_file($rfile) or die("Error: Cannot create object");
print_r($xml);
 */

$xslDoc = new DOMDocument();
$xslDoc->load("./rfc2html/rfc2html.xslt");

$xmlDoc = new DOMDocument();
$xmlDoc->load($rfile);

$proc = new XSLTProcessor();
$proc->importStylesheet($xslDoc);
// echo '<input type="text" id="search" placeholder="  live search"></input>';
echo $proc->transformToXML($xmlDoc);
?>
