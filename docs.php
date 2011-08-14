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

<?php

include 'vars.php';

echo "@prefix : <http://rdf.farmbio.uu.se/chembl/onto/#> .\n";
echo "@prefix act: <" . $rooturi . "activitiy/> .\n";
echo "@prefix res: <" . $rooturi . "resource/> .\n";
echo "@prefix mol: <" . $rooturi . "molecule/> .\n";
echo "@prefix ass: <" . $rooturi . "assay/> .\n";
echo "@prefix jrn: <" . $rooturi . "journal/> .\n";
echo "\n";

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query("SELECT DISTINCT journal FROM docs" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "jrn:j" . md5($row['journal']) . " a bibo:Journal ;\n";
  echo " dc:title \"" . $row['journal'] . "\" .\n";
}

$allIDs = mysql_query("SELECT DISTINCT * FROM docs WHERE doc_id > 0" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "res:r" . $row['doc_id'] . " a bibo:Article ;\n";
  if ($row['doi']) {
    echo " bibo:doi \"" . $row['doi'] . "\" ;\n";
    echo " owl:sameAs <http://dx.doi.org/" . $row['doi'] . "> ;\n";
  }
  if ($row['pubmed_id']) {
    echo " bibo:pmid <http://bio2rdf.org/pubmed:" . $row['pubmed_id'] . "> ;\n";
  }
  echo " dc:date \"" . $row['year'] . "\" ;\n";
  echo " bibo:volume \"" . $row['volume'] . "\" ;\n";
  echo " bibo:issue \"" . $row['issue'] . "\" ;\n";
  echo " bibo:pageStart \"" . $row['first_page'] . "\" ;\n";
  echo " bibo:pageEnd \"" . $row['last_page'] . "\" ;\n";
  echo " dc:isPartOf jrn:j" . md5($row['journal']) . " .\n";
}

?>
