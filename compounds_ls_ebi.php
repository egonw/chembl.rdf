<?php header('Content-type: text/n3');

include 'namespaces.php';
include 'functions.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

$allIDs = mysqli_query($con,
  "SELECT DISTINCT molregno, chembl_id FROM molecule_dictionary " . $ini["limit"]
);

$chebiSpace = "http://purl.obolibrary.org/obo/";

$mastervoid = $rooturi . "void.ttl#";
$masterset = $mastervoid . "ChEMBLRDF";

$thisset = $mastervoid . "ChEMBLInternalMapping";
echo triple( $thisset, $RDF . "type", $VOID . "Linkset" );
echo triple( $masterset, $VOID . "subset" , $thisset );

$molset = $mastervoid . "ChEMBLIDs";
echo triple( $molset, $RDF . "type", $VOID . "Dataset" );
echo data_triple( $molset, $VOID . "uriSpace", $CHEMBL );
echo triple( $masterset, $VOID . "subset" , $molset );

$chebiset = $mastervoid . "ChEBI";
echo triple( $chebiset, $RDF . "type", $VOID . "Dataset" );
echo data_triple( $chebiset, $VOID . "uriSpace", $chebiSpace );

echo triple( $masterset, $VOID . "subset" , $thisset );
echo "\n";
echo triple( $thisset, $DCT . "title", "ChEMBL - ChEBI OWL mappings" ) ;
echo data_triple( $thisset, $DCT . "description", "Mappings between ChEMBL compounds and the ChEBI ontology.") ;
echo triple( $thisset, $VOID . "subjectsTarget", $molset) ;
echo triple( $thisset, $VOID . "objectsTarget", $chebiset);
echo triple( $thisset, $VOID . "linkPredicate", $SKOS . "exactMatch" );
echo triple( $thisset, $DCT . "created", "2012-06-11" ) ;
echo triple( $thisset, $DCT . "license", $ini["license"] ) ;
echo "\n";

while ($row = mysqli_fetch_assoc($allIDs)) {
  $molregno = $row['molregno'];
  $molecule = $CHEMBL . $row['chembl_id'];

  # get the compound type, ChEBI, and ChEMBL identifiers
  $chebi = mysqli_query($con, "SELECT DISTINCT chebi_par_id FROM molecule_dictionary WHERE molregno = $molregno");
  if ($chebiRow = mysqli_fetch_assoc($chebi)) {
    $chebiid = $chebiRow['chebi_par_id'];
    if ($chebiid) {
      echo triple( $molecule, $SKOS . "exactMatch", $chebiSpace . "CHEBI_" . $chebiid );
    }
  }

}

?>
