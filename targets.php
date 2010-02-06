<?php header('Content-type: application/rdf+xml'); ?>
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .

@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix bibo: <http://purl.org/ontology/bibo/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

@prefix bodo: <http://www.blueobelisk.org/ontologies/chemoinformatics-algorithms/#> .
@prefix chem: <http://www.blueobelisk.org/chemistryblogs/> .
@prefix nmr: <http://www.nmrshiftdb.org/onto#> .

@prefix : <http://pele.farmbio.uu.se/chembl/onto/#> .
@prefix act: <http://rdf.farmbio.uu.se/chembl/activitiy/> .
@prefix res: <http://rdf.farmbio.uu.se/chembl/resource/> .
@prefix mol: <http://rdf.farmbio.uu.se/chembl/molecule/> .
@prefix ass: <http://rdf.farmbio.uu.se/chembl/assay/> .
@prefix trg: <http://rdf.farmbio.uu.se/chembl/target/> .
@prefix tgt: <http://rdf.farmbio.uu.se/chembl/targetType/> .

<?php 

include 'vars.php';

#$limit = " LIMIT 5";
$limit = "";

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db("chembl_02") or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM target_dictionary" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "trg:t" . $row['tid'] . " a :Target ;\n";
  echo " :hasTargetType tgt:" . $row['target_type'] . " ;\n";
  if ($row['organism'])
    echo " :organism \"" . $row['organism'] . "\" ;\n";
  if ($row['description'])
    echo " :hasDescription \"" . str_replace("\"", "\\\"", $row['description']) . "\" ;\n"; 
  if ($row['synonyms']) {
    $synonyms = preg_split("/[;]+/", $row['synonyms']);
    foreach ($synonyms as $i => $synonym) {
      echo " rdfs:label \"" . str_replace("\"", "\\\"", trim($synonym)) . "\" ;\n";
    }
  }
  if ($row['keywords']) {
    $keywords = preg_split("/[;]+/", $row['keywords']);
    foreach ($keywords as $i => $keyword) {
      echo " :hasKeyword \"" . str_replace("\"", "\\\"", trim($keyword)) . "\" ;\n";
    }
  }
  if ($row['protein_sequence'])
    echo " :sequence \"" . $row['protein_sequence'] . "\" ;\n";
  if ($row['ec_number']) {
    echo " dc:identifier \"" . $row['ec_number'] . "\" ;\n";
    echo " = <http://www.bio2rdf/ec:" . $row['ec_number'] . "> ;\n";
  }
  if ($row['protein_accession'])
    echo " = <http://www.bio2rdf/uniprot:" . $row['protein_accession'] . "> ;\n";
  if ($row['tax_id'])
    echo " :hasTaxonomy <http://www.bio2rdf/taxonomy:" . $row['tax_id'] . "> ;\n";

  # classifications
  $class = mysql_query("SELECT DISTINCT * FROM target_class WHERE tid = \"" . $row['tid'] . "\"");
  if ($classRow = mysql_fetch_assoc($class)) {
    if ($classRow['l1']) echo " :classL1 \"" . str_replace("\"", "\\\"", $classRow['l1']) . "\" ;\n";
    if ($classRow['l2']) echo " :classL2 \"" . str_replace("\"", "\\\"", $classRow['l2']) . "\" ;\n";
    if ($classRow['l3']) echo " :classL3 \"" . str_replace("\"", "\\\"", $classRow['l3']) . "\" ;\n";
    if ($classRow['l4']) echo " :classL4 \"" . str_replace("\"", "\\\"", $classRow['l4']) . "\" ;\n";
    if ($classRow['l5']) echo " :classL5 \"" . str_replace("\"", "\\\"", $classRow['l5']) . "\" ;\n";
    if ($classRow['l6']) echo " :classL6 \"" . str_replace("\"", "\\\"", $classRow['l6']) . "\" ;\n";
    if ($classRow['l7']) echo " :classL7 \"" . str_replace("\"", "\\\"", $classRow['l7']) . "\" ;\n";
    if ($classRow['l8']) echo " :classL8 \"" . str_replace("\"", "\\\"", $classRow['l8']) . "\" ;\n";
  }

  echo " dc:title \"" . $row['pref_name'] . "\" .\n";
}

?>
