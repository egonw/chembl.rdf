<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
  "SELECT DISTINCT molregno FROM molecule_dictionary " . $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $molregno = $row['molregno'];
  $molecule = $MOL . "m" . $molregno;

  # get the literature references
  $refs = mysql_query("SELECT DISTINCT doc_id FROM compound_records WHERE molregno = $molregno");
  while ($refRow = mysql_fetch_assoc($refs)) {
    if ($refRow['doc_id'])
      echo triple( $molecule, $CITO . "citesAsDataSource", $RES . "r" . $refRow['doc_id']);
  }

  # get the compound type, ChEBI, and ChEMBL identifiers
  $chebi = mysql_query("SELECT DISTINCT * FROM molecule_dictionary WHERE molregno = $molregno");
  if ($chebiRow = mysql_fetch_assoc($chebi)) {
    // The BFO SNAP MaterialEntity is the closest thing that all these things adhere too...
    // Let's hope this does not conflict with any of the used ontologies... for ChEBI we're safe, I think
    echo triple( $molecule, $RDFS . "subClassOf", $SNAP . "MaterialEntity");
    if ($chebiRow['molecule_type']) {
      if ($chebiRow['molecule_type'] == "Small molecule") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEMINF . "CHEMINF_000000"); # chemical entity
      } else if ($chebiRow['molecule_type'] == "Protein") {
        echo triple( $molecule, $RDFS . "subClassOf", $PRO . "PR_000000001" );
      } else if ($chebiRow['molecule_type'] == "Cell") {
        echo triple( $molecule, $RDFS . "subClassOf", $CL . "CL_0000000" );
      } else if ($chebiRow['molecule_type'] == "Oligosaccharide") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEBI . "CHEBI_50699");
      } else if ($chebiRow['molecule_type'] == "Oligonucleotide") {
        echo triple( $molecule, $RDFS . "subClassOf", $CHEBI . "CHEBI_7754");
      } else if ($chebiRow['molecule_type'] == "Antibody") {
        echo triple( $molecule, $OBO . "has_role", $VO . "VO_0000148" ); // Vaccin Ontology: Antibody
        echo triple( $molecule, $RDFS . "subClassOf", $PRO . "PR_000000001" );
      }
    }
    if ($chebiRow['max_phase'] == "4") {
      echo triple( $molecule, $OBO . "has_role", $CHEBI . "CHEBI_23888" ); // Drug
    }

    echo triple( $molecule, $OWL . "equivalentClass", "http://bio2rdf.org/chebi:" . $chebiRow['chebi_id'] );

    $chembl = $CHEMBL . $chebiRow['chembl_id'];
    echo triple( $chembl, $OWL . "equivalentClass", $molecule );
    echo triple( $molecule, $OWL . "equivalentClass", $chembl );
    echo data_triple( $molecule, $RDFS . "label", $chebiRow['chembl_id'] );
    $chemblChemInfRes = $chembl . "/chemblid";
    echo triple($chembl, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
    echo triple($chemblChemInfRes, $RDF . "type", $CHEMINF . "CHEMINF_000412");
    echo data_triple($chemblChemInfRes, $CHEMINF . "SIO_000300", $chebiRow['chembl_id']);
  }

  # get the structure information
  $structs = mysql_query("SELECT DISTINCT * FROM compound_structures WHERE molregno = $molregno");
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

  # get parent/child information
  $hierarchies = mysql_query("SELECT DISTINCT * FROM molecule_hierarchy WHERE molregno = $molregno");
  while ($hierarchy = mysql_fetch_assoc($hierarchies)) {
    if ($hierarchy['parent_molregno'] != $molregno) {
      $parent = $MOL . "m" . $hierarchy['parent_molregno'];
      echo triple( $molecule, $ONTO . "parentCompound", $parent );
    }
    if ($hierarchy['active_molregno'] != $molregno) {
      $child = $MOL . "m" . $hierarchy['active_molregno'];
      echo triple( $molecule, $ONTO . "activeCompound", $child );
    }
  }

}

?>
