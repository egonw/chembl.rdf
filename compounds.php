<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
  "SELECT DISTINCT * FROM compound_records " . $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $molecule = $MOL . "m" . $row['molregno'];
  if ($row['compound_name'])
    echo data_triple( $molecule, $RDFS . "label", $row['compound_name']);

  # get the compound type, ChEBI, and ChEMBL identifiers
  $chebi = mysql_query("SELECT DISTINCT * FROM molecule_dictionary WHERE molregno = \"" . $row['molregno'] . "\"");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
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

    echo triple( $molecule, $OWL . "equivalentClass", "http://bio2rdf.org/chebi:" . $chebiRow['chebi_id'] );

    $chembl = $CHEMBL . $chebiRow['chembl_id'];
    echo triple( $chembl, $OWL . "equivalentClass", $molecule );
    echo triple( $molecule, $OWL . "equivalentClass", $chembl );
    echo data_triple( $molecule, $RDFS . "label", $chebiRow['chembl_id'] );
  }

  # get the structure information
  $structs = mysql_query("SELECT DISTINCT * FROM compound_structures WHERE molregno = " . $row['molregno']);
  while ($struct = mysql_fetch_assoc($structs)) {
    if ($struct['canonical_smiles']) {
      $smiles = $struct['canonical_smiles'];
      $smiles = str_replace("\\", "\\\\", $smiles);
      $smiles = str_replace("\n", "", $smiles);
      echo data_triple( $molecule, $CHEM . "smiles", $smiles );
      $molsmiles = $molecule . "/smiles";
      echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molsmiles);
      echo triple($molsmiles, $RDF . "type", $CHEMINF . "CHEMINF_000018");
      echo data_triple($molsmiles, $CHEMINF . "SIO_000300", $smiles);
    }
    if ($struct['standard_inchi']) {
      echo data_triple($molecule, $CHEM . "inchi", $struct['standard_inchi']);
      $molsmiles = $molecule . "/inchi";
      echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molsmiles);
      echo triple($molsmiles, $RDF . "type", $CHEMINF . "CHEMINF_000113");
      echo data_triple($molsmiles, $CHEMINF . "SIO_000300", $struct['standard_inchi']);
      if (strlen($struct['standard_inchi']) < 1500) {
        echo triple($molecule, $OWL . "equivalentClass", "http://rdf.openmolecules.net/?" . $struct['standard_inchi']);
      }
    }
    if ($struct['standard_inchi_key']) {
      echo data_triple( $molecule, $CHEM . "inchikey", $struct['standard_inchi_key'] );
      $molsmiles = $molecule . "/inchikey";
      echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molsmiles);
      echo triple($molsmiles, $RDF . "type", $CHEMINF . "CHEMINF_000059");
      echo data_triple($molsmiles, $CHEMINF . "SIO_000300", $struct['standard_inchi_key']);
    }
  }

  # get the synonyms
  $names = mysql_query("SELECT DISTINCT * FROM molecule_synonyms WHERE molregno = " . $row['molregno']);
  while ($name = mysql_fetch_assoc($names)) {
    if ($name['synonyms'])
      echo data_triple( $molecule, $RDFS . "label", str_replace("\"", "\\\"", $name['synonyms']) );
  }

}

?>
