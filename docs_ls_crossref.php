<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'namespaces.php';
include 'functions.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

$allIDs = mysqli_query($con, "SELECT DISTINCT * FROM docs WHERE doc_id > 0 " . $ini["limit"]);

while ($row = mysqli_fetch_assoc($allIDs)) {
  $resource = $CHEMBL . $row['chembl_id'];
  if ($row['doi']) {
    echo triple( $resource, $OWL . "sameAs",  "http://dx.doi.org/" . $row['doi'] );
  }
}

?>
