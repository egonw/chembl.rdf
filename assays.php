<?php header('Content-type: text/n3'); ?>

<?php 

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
    "SELECT DISTINCT * FROM assays, assay_type " .
    "WHERE assays.assay_type = assay_type.assay_type" .
    $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $assay = $ASS . "a" . $row['assay_id'];
  echo triple( $assay, $RDF . "type", $ONTO . "Assay" );

  $chembl = $CHEMBL . $row['chembl_id'];
  echo data_triple( $assay, $RDFS . "label", $row['chembl_id'] );
  echo triple( $chembl, $OWL . "equivalentClass", $assay );
  echo triple( $assay, $OWL . "equivalentClass", $chembl );
  $chemblChemInfRes = $chembl . "/chemblid";
  echo triple($chembl, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
  echo triple($chemblChemInfRes, $RDF . "type", $CHEMINF . "CHEMINF_000412");
  echo data_triple($chemblChemInfRes, $CHEMINF . "SIO_000300", $row['chembl_id']);

  if ($row['assay_organism'])
    echo data_triple( $assay, $ONTO . "organism", $row['assay_organism'] );

  if ($row['description']) {
    # clean up description
    $description = $row['description'];
    $description = str_replace("\\", "\\\\", $description);
    $description = str_replace("\"", "\\\"", $description);
    echo data_triple( $assay, $ONTO . "hasDescription", $description );
  }
  if ($row['doc_id'])
    echo triple( $assay, $CITO . "citesAsDataSource", $RES . "r" . $row['doc_id'] );

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    if ($prop['tid']) {
      $targetURI = $TRG . "t" . $prop['tid'];
      echo triple( $assay, $ONTO . "hasTarget", $targetURI );
      if ($prop['confidence_score']) {
        $targetScore = $assay . "/score/t" . $prop['tid'];
        echo triple( $assay, $ONTO . "hasTargetScore", $targetScore);
        echo triple( $targetScore, $ONTO . "forTarget", $targetURI);
        echo typeddata_triple( $targetScore, $ONTO . "hasConfScore", $prop['confidence_score'], $XSD . "int" );
      }
    }
  }
  echo triple( $assay, $ONTO . "hasAssayType", $ONTO . $row['assay_desc'] );
}

?>
