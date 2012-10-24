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
  $assay = $CHEMBL . $row['chembl_id'];

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    if ($prop['tid']) {
      $targetURI = $TRG . "t" . $prop['tid'];
      if ($prop['confidence_score']) {
        $targetScore = $assay . "/score/t" . $prop['tid'];
        echo triple( $assay, $ONTO . "hasTargetScore", $targetScore);
        echo triple( $targetScore, $ONTO . "forTarget", $targetURI);
        echo typeddata_triple( $targetScore, $ONTO . "hasRelationshipType", $prop['relationship_type'], $XSD . "string" );
        echo typeddata_triple( $targetScore, $ONTO . "isComplex", $prop['complex'], $XSD . "int" );
        echo typeddata_triple( $targetScore, $ONTO . "isMulti", $prop['multi'], $XSD . "int" );
      }
    }
  }
  echo triple( $assay, $ONTO . "hasAssayType", $ONTO . $row['assay_desc'] );
}

?>
