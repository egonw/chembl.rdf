<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$NS = "http://www.openphacts.org/chembl/target/TARONT";

$root = $NS . "1";

$ontology["ops:root"] = $root;
$ontology["ops:counter"] = 1;

function level($classRow, $level, $ontology) {
  $counter = $ontology["ops:counter"];
  $higher = $ontology["ops:higher"];
  $SKOS = "http://skos.org/";
  if ($classRow[$level]) {
    $desc = $classRow[$level];
    if (!$ontology[$desc]) {
      $counter = $counter + 1;
      $ontology[$desc] = "http://www.openphacts.org/chembl/target/TARONT" . $counter;
      echo triple($higher, $SKOS . "narrower", $ontology[$desc]);
      echo data_triple($ontology[$desc], $SKOS . "prefLabel", $desc);
      $ontology["ops:counter"] = $counter;
    }
    $ontology["ops:higher"] = $ontology[$desc];
  }
  return $ontology;
}

# classifications
$class = mysql_query("SELECT DISTINCT * FROM target_class");
while ($classRow = mysql_fetch_assoc($class)) {
  $ontology["ops:higher"] = $root;
  $ontology = level($classRow, "l1", $ontology);
  $ontology = level($classRow, "l2", $ontology);
  $ontology = level($classRow, "l3", $ontology);
  $ontology = level($classRow, "l4", $ontology);
  $ontology = level($classRow, "l5", $ontology);
  $ontology = level($classRow, "l6", $ontology);
  $ontology = level($classRow, "l7", $ontology);
  $ontology = level($classRow, "l8", $ontology);
}

?>
