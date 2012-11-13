<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'namespaces.php';
include 'functions.php';
include 'to.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

$allIDs = mysqli_query($con, "SELECT DISTINCT * FROM target_dictionary " . $ini["limit"]);

function appendTo($appendTo, $string) {
  if (strlen($string) > 0) {
    return $appendTo . "/" . $string;
  }
  return $appendTo;
}

while ($row = mysqli_fetch_assoc($allIDs)) {
  $target = $CHEMBL . $row['chembl_id'];

  if ($row['protein_accession']) {
    echo triple( $target, $SKOS . "exactMatch", "http://bio2rdf.org/uniprot:" . $row['protein_accession'] );
  }

}

?>
