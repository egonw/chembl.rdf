<?php header('Content-type: text/n3'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

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
    $molecule = $MOL . "m" . $chebiRow['molregno'];
    if ($chebiRow['molecule_type']) {
      if ($chebiRow['molecule_type'] = "Small molecule") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEMINF . "CHEMINF_000000"); # chemical entity
      } else if ($chebiRow['molecule_type'] = "Protein") {
        echo triple( $molecule, $RDFS . "subClassOf", $ONTO . "Protein" );
      } else if ($chebiRow['molecule_type'] = "Cell") {
        echo triple( $molecule, $RDFS . "subClassOf", $ONTO . "Cell" );
      } else if ($chebiRow['molecule_type'] = "Oligosaccharide") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEBI . "CHEBI_50699");
      } else if ($chebiRow['molecule_type'] = "Oligonucleotide") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEBI . "CHEBI_7754");
      } else if ($chebiRow['molecule_type'] = "Antibody") {
        echo triple( $molecule, $RDFS . "subClassOf", $ONTO . "Antibody" );
      } else {
        echo triple( $molecule, $RDFS . "subClassOf", $ONTO . "Drug" );
      }
    } else {
      echo triple( $molecule, $RDFS . "subClassOf", $ONTO . "Drug" );
    }
    $structs = mysql_query("SELECT DISTINCT * FROM compound_structures WHERE molregno = " . $row['molregno']);
    while ($struct = mysql_fetch_assoc($structs)) {
      if ($struct['canonical_smiles']) {
        $smiles = $struct['canonical_smiles'];
        $smiles = str_replace("\\", "\\\\", $smiles);
        $smiles = str_replace("\n", "", $smiles);
        echo dataTriple( $molecule, $CHEM . "smiles", $smiles );
        $molsmiles = $molecule . "/smiles";
        echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molsmiles);
        echo triple($molsmiles, $RDF . "type", $CHEMINF . "CHEMINF_000018");
        echo dataTriple($molsmiles, $CHEMINF . "SIO_000300", $smiles);
      }
      if ($struct['standard_inchi']) {
        echo dataTriple($molecule, $CHEM . "inchi", $struct['standard_inchi']);
        $molsmiles = $molecule . "/inchi";
        echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molsmiles);
        echo triple($molsmiles, $RDF . "type", $CHEMINF . "CHEMINF_000113");
        echo dataTriple($molsmiles, $CHEMINF . "SIO_000300", $struct['standard_inchi']);
        if (strlen($struct['standard_inchi']) < 1500) {
          echo triple($molecule, $OWL . "equivalentClass", "http://rdf.openmolecules.net/?" . $struct['standard_inchi']);
        }
      }
      if ($struct['standard_inchi_key'])
        echo dataTriple( $molecule, $CHEM . "inchikey", $struct['standard_inchi_key'] );
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
        echo dataTriple( $molecule, $RDFS . "label", str_replace("\"", "\\\"", $name['synonyms']) );
    }

    echo triple( $molecule, $OWL . "equivalentClass", "http://bio2rdf.org/chebi:" . $chebiRow['chebi_id'] );
  }
}

?>
