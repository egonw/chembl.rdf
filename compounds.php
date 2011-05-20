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
@prefix cheminf: <http://semanticscience.org/resource/> .

@prefix : <http://pele.farmbio.uu.se/chembl/onto/#> .
@prefix act: <http://rdf.farmbio.uu.se/chembl/activitiy/> .
@prefix res: <http://rdf.farmbio.uu.se/chembl/resource/> .
@prefix mol: <http://rdf.farmbio.uu.se/chembl/molecule/> .
@prefix ass: <http://rdf.farmbio.uu.se/chembl/assay/> .

<?php

include 'vars.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
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

#foreach ($descs as $value => $type) {
#  echo "<rdf:Description rdf:about=\"" . $ns . "descriptorId=" . md5($value) . "\">\n";
#  echo "  <rdf:type rdf:resource=\"&bodo;Descriptor\" />\n";
#  echo "  <rdfs:label>" . $value . "</rdfs:label>\n";
#  echo "</rdf:Description>\n";
#}

$allIDs = mysql_query("SELECT DISTINCT compound_records.molregno FROM compound_records, compound_properties WHERE compound_records.molregno = compound_properties.molregno" . $limit);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $chebi = mysql_query("SELECT DISTINCT * FROM molecule_dictionary WHERE molregno = \"" . $row['molregno'] . "\"");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
    echo "mol:m" . $chebiRow['chebi_id'] . " a ";
    if ($chebiRow['molecule_type']) {
      if ($chebiRow['molecule_type'] = "Small molecule") {
        echo ":SmallMolecule ;\n";
      } else if ($chebiRow['molecule_type'] = "Protein") {
        echo ":Protein ;\n";
      } else if ($chebiRow['molecule_type'] = "Cell") {
        echo ":Cell ;\n";
      } else if ($chebiRow['molecule_type'] = "Oligosaccharide") {
        echo ":Oligosaccharide ;\n";
      } else if ($chebiRow['molecule_type'] = "Oligonucleotide") {
        echo ":Oligonucleotide ;\n";
      } else if ($chebiRow['molecule_type'] = "Antibody") {
        echo ":Antibody ;\n";
      } else {
        echo ":Drug ;\n";
      }
    } else {
      echo ":Drug ;\n";
    }
    $structs = mysql_query("SELECT DISTINCT * FROM compound_structures WHERE molregno = " . $row['molregno']);
    while ($struct = mysql_fetch_assoc($structs)) {
      if ($struct['canonical_smiles']) {
        $smiles = $struct['canonical_smiles'];
        $smiles = str_replace("\\", "\\\\", $smiles);
        $smiles = str_replace("\n", "", $smiles);
        echo " chem:smiles \"$smiles\" ;\n";
        echo " cheminf:CHEMINF_000200 [ a cheminf:CHEMINF_000018 ; cheminf:SIO_000300 \"$smiles\" ] ;\n";
      }
      if ($struct['standard_inchi']) {
      #if (false) {
        echo " chem:inchi \"" . $struct['standard_inchi'] . "\" ;\n";
        echo " cheminf:CHEMINF_000200 [ a cheminf:CHEMINF_000113 ; cheminf:SIO_000300 \"" . $struct['standard_inchi'] . "\" ] ;\n";
        if (strlen($struct['standard_inchi']) < 1500) {
          echo " = <http://rdf.openmolecules.net/?" . $struct['standard_inchi'] . "> ;\n";
        }
      }
      if ($struct['standard_inchi_key'])
        echo " chem:inchikey \"" . $struct['standard_inchi_key'] . "\" ;\n";
    }

    #foreach ($descs as $value => $type) {
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
    #}

    $names = mysql_query("SELECT DISTINCT * FROM molecule_synonyms WHERE molregno = " . $row['molregno']);
    while ($name = mysql_fetch_assoc($names)) {
      if ($name['synonyms'])
        echo " dc:title \"" . str_replace("\"", "\\\"", $name['synonyms']) . "\" ;\n";
    }

    echo " = <http://bio2rdf.org/chebi:" . $chebiRow['chebi_id'] . "> .\n";
  }
}

?>
