<?php header('Content-type: text/n3'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query("SELECT * FROM chembl_id_lookup WHERE entity_type = 'COMPOUND' AND chembl_id = 'CHEMBL1' ORDER BY entity_id" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $molecule = $MOL . "m" . $row['entity_id'];
  $chembl = $CHEMBL . $row['chembl_id'];

  echo triple( $chembl, $OWL . "equivalentClass", $molecule );
  echo triple( $molecule, $OWL . "equivalentClass", $chembl );
  echo dataTriple( $molecule, $DC . "title", $row['chembl_id'] );
  echo dataTriple( $molecule, $RDFS . "label", $row['chembl_id'] );
}

?>
