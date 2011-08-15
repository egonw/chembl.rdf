<?php header('Content-type: text/n3'); ?>

<?php 

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
echo "# Connection to the server was successful!\n";

mysql_select_db($db) or die(mysql_error());
echo "# Database " . $db . " was selected!\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM activities" . $limit);

while ($row = mysql_fetch_assoc($allIDs)) {
  $activity = $ACT . "a" . $row['activity_id'];
  echo triple( $activity, $RDF . "type",  $ONTO . "Activity" );
  echo triple( $activity, $CITO . "citesAsDataSource", $RES . "r" . $row['doc_id'] );
  echo triple( $activity, $ONTO . "onAssay", $ASS . "a" . $row['assay_id'] );
  $chebi = mysql_query("SELECT DISTINCT * FROM molecule_dictionary WHERE molregno = \"" . $row['molregno'] . "\"");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
    echo triple( $activity, $ONTO . "forMolecule", $MOL . "m" . $chebiRow['chebi_id'] );
  }
  if ($row['relation']) {
    echo dataTriple( $activity, $ONTO . "relation",  $row['relation'] );
  }
  if ($row['standard_value']) {
    echo typeddataTriple( $activity, $ONTO . "standardValue", $row['standard_value'], $XSD . "float" );
    echo dataTriple( $activity, $ONTO . "standardUnits", $row['standard_units'] );
    echo dataTriple( $activity, $ONTO . "type",  $row['standard_type'] );
  }
  flush();
}

?>
