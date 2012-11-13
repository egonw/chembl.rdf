<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysqli_connect("localhost", $user, $pwd) or die(mysqli_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysqli_select_db($db) or die(mysqli_error());
# echo "<!-- Database was selected! -->\n";

$NS = "http://www.openphacts.org/chembl/target/TARONT";

$root = $NS . "1";

$ontology["ops:root"] = $root;
$ontology["ops:counter"] = 1;
$ontology["ops:counter2"] = 1000;

function level($classRow, $level, $ontology) {
  $counter = $ontology["ops:counter"];
  $counter2 = $ontology["ops:counter2"];
  $higher = $ontology["ops:higher"];
  $stack = $ontology["ops:stack"];
  $classification = $classRow["target_classification"];
  if ($classRow[$level]) {
    $desc = $classRow[$level];
    $stack = $stack . "/" . $desc;
    if (!$ontology[$desc]) {
      // new label -> new ID, TARONT < 1000
      $counter = $counter + 1;
      $ontology[$desc] = "http://www.openphacts.org/chembl/target/TARONT" . $counter;
      $ontology[$stack] = "http://www.openphacts.org/chembl/target/TARONT" . $counter;
      echo " \"" . $stack . "\" => [\n";
      echo "  \"uri\" => \"" . $ontology[$stack] . "\",\n";
      echo "  \"higher\" => \"" . $higher . "\",\n";
      echo "  \"classification\" => \"" . $classification . "\",\n";
      // echo "  \"stack\" => \"" . $stack . "\",\n";
      echo "  \"level\" => \"" . $level . "\",\n";
      echo "  \"label\" => \"" . $desc . "\",\n";
      echo " ],\n";
      $ontology["ops:counter"] = $counter;
    } else  if (!$ontology[$stack]) {
      // old label, new stack -> new ID, TARONT > 1000
      $counter2 = $counter2 + 1;
      $ontology[$stack] = "http://www.openphacts.org/chembl/target/TARONT" . $counter2;
      echo " \"" . $stack . "\" => [\n";
      echo "  \"uri\" => \"" . $ontology[$stack] . "\",\n";
      echo "  \"higher\" => \"" . $higher . "\",\n";
      echo "  \"classification\" => \"" . $classification . "\",\n";
      // echo "  \"stack\" => \"" . $stack . "\",\n";
      echo "  \"level\" => \"" . $level . "\",\n";
      echo "  \"label\" => \"" . $desc . "\",\n";
      echo " ],\n";
      $ontology["ops:counter2"] = $counter2;
    } // else: old label, old stack -> no new entry
    $ontology["ops:higher"] = $ontology[$stack];
    $ontology["ops:stack"] = $stack;
  }
  return $ontology;
}

echo "<?php\n";
echo "\$array = [\n";

# classifications
$class = mysqli_query("SELECT DISTINCT * FROM target_class");
while ($classRow = mysqli_fetch_assoc($class)) {
  $ontology["ops:higher"] = $root;
  $ontology["ops:stack"] = "";
  $ontology = level($classRow, "l1", $ontology);
  $ontology = level($classRow, "l2", $ontology);
  $ontology = level($classRow, "l3", $ontology);
  $ontology = level($classRow, "l4", $ontology);
  $ontology = level($classRow, "l5", $ontology);
  $ontology = level($classRow, "l6", $ontology);
  $ontology = level($classRow, "l7", $ontology);
  $ontology = level($classRow, "l8", $ontology);
}

echo "];\n";
echo "?>\n";

?>
