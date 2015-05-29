<?php

$cfg=parse_ini_file("rfc2html.cfg", true);
//print_r($cfg);

if ( !empty ($cfg['Global']['phphead']) ) 
    include  $cfg['Global']['phphead'];
else {
    echo "<html> <head> <body>"; }

foreach ( $cfg['index']['js'] as $js ) 
    echo '<script type="text/javascript" charset="utf8" src="'.$js.'"></script>';
foreach ( $cfg['index']['stylesheets'] as $css ) 
    echo '<link rel="stylesheet" type="text/css" href="'.$css.'">';


$rfile=$cfg['Global']['RFCPATH']."/rfc-index.xml";
echo <<<EOT
<script>
 $(document).ready(function () {
var table = $('#Table').DataTable();
table.columns().flatten().each( function ( colIdx ) {
    // Create the select list and search operation
    var select = $('<select />')
        .appendTo(
            table.column(colIdx).footer()
        )
        .on( 'change', function () {
            table
                .column( colIdx )
                .search( $(this).val() )
                .draw();
        } );

    // Get the search data for the first column and add to the select list
    table
        .column( colIdx )
        .cache( 'search' )
        .sort()
        .unique()
        .each( function ( d ) {
            select.append( $('<option value="'+d+'">'+d+'</option>') );
        } );
} );

});

</script>
EOT;
$xslDoc = new DOMDocument();
$xslDoc->load($cfg['index']['xslt']);

$xmlDoc = new DOMDocument();
$xmlDoc->load($rfile);

$proc = new XSLTProcessor();
$proc->importStylesheet($xslDoc);
echo $proc->transformToXML($xmlDoc);
if ( ! empty ($cfg['Global']['phptail']) ) 
    include  $cfg['Global']['phptail'];
else {
    echo " </body> </head> </html> ";
}

?>
