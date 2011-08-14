<?php header('Content-type: text/n3'); ?>
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .

@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix bibo: <http://purl.org/ontology/bibo/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema-datatypes> .

@prefix bodo: <http://www.blueobelisk.org/ontologies/chemoinformatics-algorithms/#> .
@prefix chem: <http://www.blueobelisk.org/chemistryblogs/> .
@prefix nmr: <http://www.nmrshiftdb.org/onto#> .

@prefix : <http://pele.farmbio.uu.se/chembl/onto/#> .
@prefix act: <http://rdf.farmbio.uu.se/chembl/activity/> .
@prefix res: <http://rdf.farmbio.uu.se/chembl/resource/> .
@prefix mol: <http://rdf.farmbio.uu.se/chembl/molecule/> .
@prefix ass: <http://rdf.farmbio.uu.se/chembl/assay/> .
@prefix trg: <http://rdf.farmbio.uu.se/chembl/target/> .
@prefix trt: <http://rdf.farmbio.uu.se/chembl/targetType/> .

<?php 

include 'vars.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
    "SELECT DISTINCT * FROM assays, assay_type " .
    "WHERE assays.assay_type = assay_type.assay_type" .
    $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "ass:a" . $row['assay_id'] . " a :Assay ;\n";
  if ($row['description']) {
    # clean up description
    $description = $row['description'];
    $description = str_replace("\\", "\\\\", $description);
    $description = str_replace("\"", "\\\"", $description);
    echo " :hasDescription \"$description\" ;\n";
  }
  if ($row['doc_id'])
    echo " :extractedFrom res:r" . $row['doc_id'] . " ;\n";

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    if ($prop['assay_organism'])
      echo " :organism \"" . $prop['assay_organism'] . "\" ;\n";
    if ($prop['tid'])
      echo " :hasTarget trg:t" . $prop['tid'] . " ;\n";
    if ($prop['confidence_score'])
      echo " :hasConfScore \"" . $prop['confidence_score'] . "\"^^xsd:integer ;\n";
  }
  echo " :hasAssayType :" . $row['assay_desc'] . " .\n";
}

?>
