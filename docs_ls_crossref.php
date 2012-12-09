<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'namespaces.php';
include 'functions.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$importedBy = $ini["importedBy"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

# VOID
$mastervoid = $rooturi . "void.ttl#";
$masterset = $mastervoid . "ChEMBLRDF";
$thisset = $mastervoid . "ChEMBLCrossRefMapping";
$thisSetTitle = "ChEMBL - CrossRef mappings";
$thisSetDescription = "Mappings between ChEMBL documents and CrossRef.";
$sourceSet = $mastervoid . "ChEMBLIDs";
$sourceSpace = $CHEMBL;
$targetSet = $mastervoid . "CrossRef";
$targetSpace = "http://dx.doi.org/";
$linkPredicate = $OWL . "sameAs";
$expresses = "http://purl.obolibrary.org/obo/MI_0574";

$current_date = gmDate("Y-m-d\TH:i:s");
echo triple( $thisset , $PAV . "createdBy",  $importedBy );
echo typeddata_triple( $thisset, $PAV . "createdOn", $current_date,  $XSD . "dateTime" );
echo triple( $thisset , $PAV . "authoredBy",  $importedBy );
echo typeddata_triple( $thisset, $PAV . "authoredOn", $current_date,  $XSD . "dateTime" );
echo triple( $thisset, $RDF . "type", $VOID . "Linkset" );
echo triple( $masterset, $VOID . "subset" , $thisset );

# echo triple( $molset, $RDF . "type", $VOID . "Dataset" );
echo data_triple( $sourceSet, $VOID . "uriSpace", $sourceSpace );
echo triple( $masterset, $VOID . "subset" , $sourceSet );

# echo triple( $chebiset, $RDF . "type", $VOID . "Dataset" );
echo data_triple( $targetSet, $VOID . "uriSpace", $targetSpace );

echo triple( $masterset, $VOID . "subset" , $thisset );
echo "\n";
echo data_triple( $thisset, $DCT . "title", $thisSetTitle ) ;
echo data_triple( $thisset, $DCT . "description", $thisSetDescription ) ;
echo triple( $thisset, $VOID . "subjectsTarget", $sourceSet) ;
echo triple( $thisset, $VOID . "objectsTarget", $targetSet);
echo triple( $thisset, $VOID . "linkPredicate", $linkPredicate );
echo triple( $thisset, $DCT . "license", $ini["license"] ) ;
echo triple( $thisset, $DUL . "expresses", $expresses);
echo "\n";

# DATA
$allIDs = mysqli_query($con, "SELECT DISTINCT * FROM docs WHERE doc_id > 0 AND doi IS NOT NULL " . $ini["limit"]);

while ($row = mysqli_fetch_assoc($allIDs)) {
  $resource = $CHEMBL . $row['chembl_id'];
  if ($row['doi']) {
    echo triple( $resource, $OWL . "sameAs",  "http://dx.doi.org/" . $row['doi'] );
  }
}

?>
