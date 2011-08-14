<?php header('Content-type: text/n3'); ?>
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .

@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix bibo: <http://purl.org/ontology/bibo/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

@prefix bodo: <http://www.blueobelisk.org/ontologies/chemoinformatics-algorithms/#> .
@prefix chem: <http://www.blueobelisk.org/chemistryblogs/> .
@prefix nmr: <http://www.nmrshiftdb.org/onto#> .

<?php 

include 'vars.php';

echo "@prefix : <http://rdf.farmbio.uu.se/chembl/onto/#> .\n";
echo "@prefix act: <" . $rooturi . "activity/> .\n";
echo "@prefix res: <" . $rooturi . "resource/> .\n";
echo "@prefix mol: <" . $rooturi . "molecule/> .\n";
echo "@prefix ass: <" . $rooturi . "assay/> .\n";
echo "@prefix jrn: <" . $rooturi . "journal/> .\n";
echo "@prefix trg: <" . $rooturi . "target/> .\n";
echo "@prefix tgt: <" . $rooturi . "targetType/> .\n";
echo "\n";

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
echo "# Connection to the server was successful!\n";

mysql_select_db($db) or die(mysql_error());
echo "# Database " . $db . " was selected!\n";

$allIDs = mysql_query("SELECT DISTINCT * FROM activities" . $limit);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "act:a" . $row['activity_id'] . " a :Activity ;\n";
  echo " :extractedFrom res:r" . $row['doc_id'] . " ;\n";
  echo " :onAssay ass:a" . $row['assay_id'] . " ;\n";
  $chebi = mysql_query("SELECT DISTINCT * FROM molecule_dictionary WHERE molregno = \"" . $row['molregno'] . "\"");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
    echo " :forMolecule mol:m" . $chebiRow['chebi_id'] . " ";
  }
  if ($row['relation']) {
    echo ";\n :relation \"" . $row['relation'] . "\" ";
  }
  if ($row['standard_value']) {
    echo ";\n :standardValue \"" . $row['standard_value'] . "\"^^xsd:float ;\n";
    echo " :standardUnits \"" . $row['standard_units'] . "\" ;\n";
    echo " :type \"" . $row['standard_type'] . "\" ";
  }
  echo ".\n";
  flush();
}

?>
