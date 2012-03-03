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
  if ($row['description']) {
    # clean up description
    $description = $row['description'];
    $description = str_replace("\\", "\\\\", $description);
    $description = str_replace("\"", "\\\"", $description);
    echo data_triple( $assay, $ONTO . "hasDescription", $description );
  }
  if ($row['doc_id'])
    echo triple( $assay, $ONTO . "extractedFrom", $RES . "r" . $row['doc_id'] );

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    if ($prop['tid'])
      echo triple( $assay, $ONTO . "hasTarget", $TRG . "t" . $prop['tid'] );
    if ($prop['confidence_score'])
      echo typeddata_triple( $assay, $ONTO . "hasConfScore", $prop['confidence_score'], $XSD . "int" );
  }
  echo triple( $assay, $ONTO . "hasAssayType", $ONTO . $row['assay_desc'] );
}

?>
