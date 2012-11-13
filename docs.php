<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'namespaces.php';
include 'functions.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

$allIDs = mysqli_query($con, "SELECT DISTINCT journal FROM docs WHERE doc_id > 0 " . $ini["limit"]);

while ($row = mysqli_fetch_assoc($allIDs)) {
  if (strlen($row['journal']) > 0) {
    echo triple($JRN . "j" . md5($row['journal']), $RDF . "type", $BIBO . "Journal");
    echo data_triple($JRN . "j" . md5($row['journal']), $DC . "title", $row['journal']);
  }
}
echo "\n";

$allIDs = mysqli_query($con, "SELECT DISTINCT * FROM docs WHERE doc_id > 0 " . $ini["limit"]);

while ($row = mysqli_fetch_assoc($allIDs)) {
  $resource = $CHEMBL . $row['chembl_id'];
  echo triple( $resource, $RDF . "type", $BIBO . "Article" );
  if ($row['doi']) {
    echo data_triple( $resource, $BIBO . "doi", $row['doi'] );
  }
  if ($row['pubmed_id']) {
    echo data_triple( $resource, $BIBO . "pmid", $row['pubmed_id'] );
  }
  echo data_triple( $resource, $DC . "date", $row['year'] );
  echo data_triple( $resource, $BIBO . "volume", $row['volume'] );
  echo data_triple( $resource, $BIBO . "issue", $row['issue'] );
  echo data_triple( $resource, $BIBO . "pageStart", $row['first_page'] );
  echo data_triple( $resource, $BIBO . "pageEnd", $row['last_page'] );
  echo triple( $resource, $DC . "isPartOf", $JRN . "j" . md5($row['journal']) );
}

?>
