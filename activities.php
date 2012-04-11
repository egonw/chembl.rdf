<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

$unitMappings = [
  "nM" => $OPS . "Nanomolar",
  "mM" => $OPS . "Millimolar",
  "uM" => $OPS . "Micromolar",
  "pM" => $OPS . "Picomolar",
  "%"  => $QUDT . "floatPercentage",
];

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
echo "# Connection to the server was successful!\n";

mysql_select_db($db) or die(mysql_error());
echo "# Database " . $db . " was selected!\n\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM activities" . $limit);

while ($row = mysql_fetch_assoc($allIDs)) {
  $activity = $ACT . "a" . $row['activity_id'];
  echo triple( $activity, $RDF . "type",  $ONTO . "Activity" );
  if ($row['doc_id'] != '-1') {
    echo triple( $activity, $CITO . "citesAsDataSource", $RES . "r" . $row['doc_id'] );
  }
  echo triple( $activity, $ONTO . "onAssay", $ASS . "a" . $row['assay_id'] );
  echo triple( $activity, $ONTO . "forMolecule", $MOL . "m" . $row['molregno'] );
  if ($row['relation']) {
    echo data_triple( $activity, $ONTO . "relation",  $row['relation'] );
  }
  if ($row['standard_value']) {
    echo typeddata_triple( $activity, $ONTO . "standardValue", $row['standard_value'], $XSD . "float" );
    echo data_triple( $activity, $ONTO . "type",  $row['standard_type'] );
    $units = $row['standard_units'];
    echo data_triple( $activity, $ONTO . "standardUnits", $units );
    if (array_key_exists($units, $unitMappings)) {
      echo triple( $activity, $ONTO . "standardsUnitsClass", $OPS . $unitMappings[$units] );
    }
  }
  flush();
}

?>
