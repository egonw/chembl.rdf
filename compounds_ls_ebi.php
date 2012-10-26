<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
  "SELECT DISTINCT molregno FROM compound_records " . $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $molregno = $row['molregno'];
  $molecule = $MOL . "m" . $molregno;

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
