<?php header('Content-type: application/rdf+xml');
print("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"); 
?>
<!DOCTYPE rdf:RDF [
  <!ENTITY gt "&#62;">
  <!ENTITY lt "&#60;">
  <!ENTITY ch "http://pele.farmbio.uu.se/chembl/?">
  <!ENTITY bodo "http://www.blueobelisk.org/ontologies/chemoinformatics-algorithms/#">
]>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:nmr="http://www.nmrshiftdb.org/onto#"
         xmlns:chembl="&ch;"
         xmlns:chem="http://www.blueobelisk.org/chemistryblogs/"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:foaf="http://xmlns.com/foaf/0.1/"
         xmlns:bodo="&bodo;"
         xmlns:owl="http://www.w3.org/2002/07/owl#"
         xmlns:bibo="http://purl.org/ontology/bibo/">

<?php 

include 'vars.php';

$ns = "&ch;";

#$limit = " LIMIT 5";
$limit = "";

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db("chembl_02") or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$relations = array(
  ">" => "RelationGT",
  "=" => "RelationEQ",
  ">=" => "RelationGE",
  "<" => "RelationLT"
);
$relationLabels = array(
  "<" => "&lt;",
  "=" => "=",
  ">" => "&gt;",
  ">=" => "&gt;="
);

foreach ($relations as $value => $type) {
  echo "<rdf:Description rdf:about=\"" . $ns . "relationId=" . $type . "\">\n";
  echo "  <rdf:type rdf:resource=\"" . $ns . "RelationType\" />\n";
  echo "  <rdfs:label>" . $relationLabels[$value] . "</rdfs:label>\n";
  echo "</rdf:Description>\n";
}

$allIDs = mysql_query("SELECT DISTINCT * FROM activities" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "<rdf:Description rdf:about=\"" . $ns . "activityId=" . $row['activity_id'] . "\">\n";
  echo "  <rdf:type rdf:resource=\"" . $ns . "Activity\" />\n";
  echo "  <chembl:extractedFrom rdf:resource=\"" . $ns . "resourceId=" . $row['doc_id'] . "\" />\n";
  echo "  <chembl:onAssay rdf:resource=\"" . $ns . "assayId=" . $row['assay_id'] . "\" />\n";
  echo "  <chembl:forMolecule rdf:resource=\"" . $ns . "moleculeId=" . $row['molregno'] . "\" />\n";
  if ($row['relation']) {
    if ($relations[$row['relation']])
      echo "  <chembl:relation rdf:resource=\"" . $ns . "relationId=" . $relations[$row['relation']] . "\" />\n";
  }
  echo "  <chembl:standardValue>" . $row['standard_value'] . "</chembl:standardValue>\n";
  echo "  <chembl:standardUnits>" . $row['standard_units'] . "</chembl:standardUnits>\n";
  echo "  <chembl:type>" . $row['standard_type'] . "</chembl:type>\n";  
  echo "</rdf:Description>\n";
}

?>

</rdf:RDF>
