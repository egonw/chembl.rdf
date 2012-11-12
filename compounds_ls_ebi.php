<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
  "SELECT DISTINCT molregno, chembl_id FROM molecule_dictionary " . $limit
);

$num = mysql_numrows($allIDs);

$mastervoid = $rooturi . "void.ttl#";
$masterset = $mastervoid . "ChEMBLRDF";
$thisset = $mastervoid . "ChEMBLInternalMapping";
echo triple( $thisset, $RDF . "type", $VOID . "Linkset" );
$chebiset = $mastervoid . "ChEBI";
echo triple( $chebiset, $RDF . "type", $VOID . "Dataset" );

echo triple( $masterset, $VOID . "subset" , $thisset );
echo "\n";
echo triple( $thisset, $DCT . "title", "ChEMBL - ChEBI OWL mappings" ) ;
echo triple( $thisset, $DCT . "description", "Mappings between ChEMBL compounds and the ChEBI ontology.") ;
echo triple( $thisset, $VOID . "subjectsTarget", $thisset) ;
echo triple( $thisset, $VOID . "objectsTarget", $chebiset);
echo triple( $thisset, $VOID . "linkPredicate", $SKOS . "exactMatch" );
echo triple( $thisset, $DCT . "created", "2012-06-11" ) ;
echo triple( $thisset, $DCT . "license", $license ) ;
echo "\n";

while ($row = mysql_fetch_assoc($allIDs)) {
  $molregno = $row['molregno'];
  $molecule = $CHEMBL . $row['chembl_id'];

  # get the compound type, ChEBI, and ChEMBL identifiers
  $chebi = mysql_query("SELECT DISTINCT chebi_par_id FROM molecule_dictionary WHERE molregno = $molregno");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
    $chebiid = $chebiRow['chebi_par_id'];
    if ($chebiid) {
      echo triple( $molecule, $SKOS . "exactMatch", "http://purl.obolibrary.org/obo/CHEBI_" . $chebiid );
    }
  }

}

?>
