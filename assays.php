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

$allIDs = mysql_query(
    "SELECT DISTINCT * FROM assays, assay_type " .
    "WHERE assays.assay_type = assay_type.assay_type" .
    $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "<rdf:Description rdf:about=\"" . $ns . "assayId=" . $row['assay_id'] . "\">\n";
  echo "  <rdf:type rdf:resource=\"" . $ns . "Assay\" />\n";
  echo "  <chembl:hasAssayType rdf:resource=\"" . $ns . $row['assay_desc'] . "\" />\n";
  echo "  <chembl:extractedFrom rdf:resource=\"" . $ns . "resourceId=" . $row['doc_id'] . "\" />\n";

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    echo "  <chembl:organism>" . $prop['assay_organism'] . "</chembl:organism>\n";
    echo "  <chembl:hasTarget rdf:resource=\"" . $ns . "tid=" . $prop['tid'] . "\" />\n";
  }

  echo "</rdf:Description>\n";
}

?>

</rdf:RDF>
