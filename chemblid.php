<?php header('Content-type: application/rdf+xml');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

$con = mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$id = "1000000";

$allIDs = mysql_query(
  "SELECT * FROM chembl_id_lookup WHERE chembl_id = \"CHEMBL$id\""
);

if ($row = mysql_fetch_assoc($allIDs)) {
  $resource = $CHEMBL . $row['chembl_id'];
  echo data_triple( $resource, $RDFS . "label", $row['chembl_id'] );
  $chemblChemInfRes = $resource . "/chemblid";
  echo triple($resource, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
  echo triple($chemblChemInfRes, $RDF . "type", $CHEMINF . "CHEMINF_000412");
  echo data_triple($chemblChemInfRes, $CHEMINF . "SIO_000300", $row['chembl_id']);

  $entityType = $row['entity_type'];
  if ($entityType == "ASSAY") {
    $mol = $ASS . "a" . $row['entity_id'];
    echo triple( $resource, $OWL . "sameAs", $mol );
  } else if ($entityType == "COMPOUND") {
    $mol = $MOL . "m" . $row['entity_id'];
    echo triple( $resource, $OWL . "equivalentClass", $mol );
  } else if ($entityType == "DOCUMENT") {
    $mol = $RES . "r" . $row['entity_id'];
    echo triple( $resource, $OWL . "sameAs", $mol );
  } else if ($entityType == "TARGET") {
    $mol = $TRG . "t" . $row['entity_id'];
    echo triple( $resource, $OWL . "equivalentClass", $mol );
  }
}

?>
