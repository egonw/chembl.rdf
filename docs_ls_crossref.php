<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM docs WHERE doc_id > 0 " . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $resource = $CHEMBL . $row['chembl_id'];
  if ($row['doi']) {
    echo data_triple( $resource, $BIBO . "doi", $row['doi'] );
    echo triple( $resource, $OWL . "sameAs",  "http://dx.doi.org/" . $row['doi'] );
  }
}

?>
