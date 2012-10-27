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
  $target = $TRG . "t" . $row['tid'];
  echo triple( $target, $RDF . "type", $ONTO . "Target" );
  if ($row['target_type'] == 'PROTEIN') {
    echo triple( $target, $RDFS . "subClassOf", $PRO . "PR_000000001" );
  } else {
    echo triple( $target, $RDFS . "subClassOf", $TGT . $row['target_type'] );
  }

  $chembl = $CHEMBL . $row['chembl_id'];
  echo triple( $chembl, $OWL . "equivalentClass", $target );
  echo triple( $target, $OWL . "equivalentClass", $chembl );
  echo data_triple( $target, $RDFS . "label", $row['chembl_id'] );
  $chemblChemInfRes = $chembl . "/chemblid";
  echo triple($chembl, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
  echo triple($chemblChemInfRes, $RDF . "type", $CHEMINF . "CHEMINF_000412");
  echo data_triple($chemblChemInfRes, $CHEMINF . "SIO_000300", $row['chembl_id']);

  if ($row['organism'])
    echo data_triple( $target, $ONTO . "organism", $row['organism'] );
  if ($row['description'])
    echo data_triple( $target, $ONTO . "hasDescription",  str_replace("\"", "\\\"", $row['description']) ); 
  if ($row['synonyms']) {
    $synonyms = preg_split("/[;]+/", $row['synonyms']);
    foreach ($synonyms as $i => $synonym) {
      echo data_triple( $target, $RDFS . "label", str_replace("\"", "\\\"", trim($synonym)) );
    }
  }
  if ($row['keywords']) {
    $keywords = preg_split("/[;]+/", $row['keywords']);
    foreach ($keywords as $i => $keyword) {
      echo data_triple( $target, $ONTO . "hasKeyword", str_replace("\"", "\\\"", trim($keyword)) );
    }
  }
  if ($row['protein_sequence'])
    echo data_triple( $target, $ONTO . "sequence", $row['protein_sequence'] );
  if ($row['ec_number']) {
    echo data_triple( $target, $DC . "identifier", $row['ec_number'] );
    echo triple( $target, $RDFS . "subClassOf", "http://bio2rdf.org/ec:" . $row['ec_number'] );
    echo triple( $target, $SKOS . "exactMatch", $ENZYME . $row['ec_number'] );
  }
  if ($row['protein_accession']) {
    echo data_triple( $target, $DC . "identifier",  "uniprot:" . $row['protein_accession'] );
  }
  if ($row['tax_id'])
    echo triple( $target, $ONTO . "hasTaxonomy", "http://bio2rdf.org/taxonomy:" . $row['tax_id'] );

  # classifications
  $class = mysql_query("SELECT DISTINCT * FROM target_class WHERE tid = \"" . $row['tid'] . "\"");
  if ($classRow = mysql_fetch_assoc($class)) {
    $hier = "";
    if ($classRow['l1']) $hier = appendTo($hier, $classRow['l1']);
    if ($classRow['l2']) $hier = appendTo($hier, $classRow['l2']);
    if ($classRow['l3']) $hier = appendTo($hier, $classRow['l3']);
    if ($classRow['l4']) $hier = appendTo($hier, $classRow['l4']);
    if ($classRow['l5']) $hier = appendTo($hier, $classRow['l5']);
    if ($classRow['l6']) $hier = appendTo($hier, $classRow['l6']);
    if ($classRow['l7']) $hier = appendTo($hier, $classRow['l7']);
    if ($classRow['l8']) $hier = appendTo($hier, $classRow['l8']);
    echo triple( $target, $ONTO . "targetClass", $array[$hier]["uri"] );
  }

  if ($row['pref_name'])
    echo data_triple( $target, $DC . "title", $row['pref_name'] );
}

?>
