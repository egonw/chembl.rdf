<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'namespaces.php';
include 'functions.php';
include 'to.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$importedBy = $ini["importedBy"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

# VOID
$mastervoid = $rooturi . "void.ttl#";
$masterset = $mastervoid . "ChEMBLRDF";
$thisset = $mastervoid . "ChEMBLBio2RDFMapping";
$thisSetTitle = "ChEMBL Target - Bio2RDF mappings";
$thisSetDescription = "Mappings between ChEMBL targets and Bio2RDF.";
$sourceSet = $mastervoid . "ChEMBLTarget";
$sourceSpace = $CHEMBL;
$targetSet = $mastervoid . "Bio2RDF";
$targetSpace = "http://bio2rdf.org/uniprot:";
$linkPredicate = $SKOS . "exactMatch";
$expresses = "http://lsdis.cs.uga.edu/projects/glycomics/propreo#UNIPROT_accession_number";

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
$allIDs = mysqli_query($con, "SELECT DISTINCT * FROM target_dictionary " . $ini["limit"]);

function appendTo($appendTo, $string) {
  if (strlen($string) > 0) {
    return $appendTo . "/" . $string;
  }
  return $appendTo;
}

while ($row = mysqli_fetch_assoc($allIDs)) {
  $target = $CHEMBL . $row['chembl_id'];

  if ($row['protein_accession']) {
    echo triple( $target, $SKOS . "exactMatch", "http://bio2rdf.org/uniprot:" . $row['protein_accession'] );
  }

}

?>
