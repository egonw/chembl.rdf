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

$descs = array(
  "alogp" => "double",
  "hba" => "nonNegativeInteger",
  "hbd" => "nonNegativeInteger",
  "psa" => "double",
  "rtb" => "nonNegativeInteger",
  "ro3_pass" => "boolean",
  "num_ro5_violations" => "nonNegativeInteger",
  "med_chem_friendly" => "boolean"
);

foreach ($descs as $value => $type) {
  echo "<rdf:Description rdf:about=\"" . $ns . "descriptorId=" . md5($value) . "\">\n";
  echo "  <rdf:type rdf:resource=\"&bodo;Descriptor\" />\n";
  echo "  <rdfs:label>" . $value . "</rdfs:label>\n";
  echo "</rdf:Description>\n";
}

$allIDs = mysql_query("SELECT * FROM compounds, compound_properties WHERE compounds.molregno = compound_properties.molregno" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  echo "<rdf:Description rdf:about=\"" . $ns . "moleculeId=" . $row['molregno'] . "\">\n";
  echo "  <rdf:type rdf:resource=\"" . $ns . "Compound\" />\n";
  if ($row['inchi']) {
    echo "  <owl:sameAs rdf:resource=\"http://rdf.openmolecules.net/?" . $row['inchi'] . "\" />\n";
    echo "  <chem:inchi>" . $row['inchi'] . "</chem:inchi>\n";
  }
  if ($row['chebi_id'])
    echo "  <owl:sameAs rdf:resource=\"http://bio2rdf.org/ebi:" . $row['chebi_id'] . "\" />\n";
  if ($row['inchi_key'])
    echo "  <chem:inchikey>" . $row['inchi_key'] . "</chem:inchikey>\n";
  if ($row['canonical_smiles'])
    echo "  <chem:smiles>" . $row['canonical_smiles'] . "</chem:smiles>\n";
  foreach ($descs as $value => $type) {
    #if ($row[$row[$value]]) {
    if (false) {
      echo "  <bodo:hasDescriptorValue>\n";
      echo "    <bodo:DescriptorValue>\n";
      echo "      <bodo:hasPart>\n";
      echo "        <bodo:DescriptorValuePoint>\n";
      echo "          <bodo:hasValue rdf:datatype=\"http://www.w3.org/2001/XMLSchema#" .
           $type . "\">" . $row[$value] . "</bodo:hasValue>\n";
      echo "          <bodo:valuePointFor rdf:resource=\"" . $ns . "descriptorId=" . md5($value) . "\" />\n";
      echo "        </bodo:DescriptorValuePoint>\n";
      echo "      </bodo:hasPart>\n";
      echo "    </bodo:DescriptorValue>\n";
      echo "  </bodo:hasDescriptorValue>\n";
    }
  }

  $names = mysql_query("SELECT DISTINCT * FROM compound_synonyms WHERE molregno = " . $row['molregno']);
  while ($name = mysql_fetch_assoc($names)) {
    if ($name['synonyms'])
      echo "  <dc:title>" . $name['synonyms'] . "</dc:title>\n";
  }
  echo "</rdf:Description>\n";
}

?>

</rdf:RDF>
