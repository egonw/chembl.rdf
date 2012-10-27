<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';
include 'to.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM target_dictionary" . $limit);

$num = mysql_numrows($allIDs);

function appendTo($appendTo, $string) {
  if (strlen($string) > 0) {
    return $appendTo . "/" . $string;
  }
  return $appendTo;
}

while ($row = mysql_fetch_assoc($allIDs)) {
  $target = $CHEMBL . $row['chembl_id'];

  if ($row['protein_accession']) {
    echo triple( $target, $SKOS . "exactMatch", "http://bio2rdf.org/uniprot:" . $row['protein_accession'] );
  }

}

?>
