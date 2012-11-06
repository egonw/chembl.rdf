<?php header('Content-type: text/n3'); ?>

<?php 

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
    "SELECT DISTINCT * FROM assays " .
    $limit
);

$num = mysql_numrows($allIDs);

while ($row = mysql_fetch_assoc($allIDs)) {
  $assay = $CHEMBL . $row['chembl_id'];
  echo triple( $assay, $RDF . "type", $ONTO . "Assay" );

  $chembl = $CHEMBL . $row['chembl_id'];
  echo data_triple( $assay, $RDFS . "label", $row['chembl_id'] );
  echo triple( $chembl, $OWL . "equivalentClass", $assay );
  echo triple( $assay, $OWL . "equivalentClass", $chembl );
  $chemblChemInfRes = $chembl . "/chemblid";
  echo triple($chembl, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
  echo triple($chemblChemInfRes, $RDF . "type", $CHEMINF . "CHEMINF_000412");
  echo data_triple($chemblChemInfRes, $CHEMINF . "SIO_000300", $row['chembl_id']);

  if ($row['assay_organism'])
    echo data_triple( $assay, $ONTO . "organism", $row['assay_organism'] );
  if ($row['assay_tax_id'])
    echo triple( $assay, $ONTO . "hasTaxonomy", "http://bio2rdf.org/taxonomy:" . $row['assay_tax_id'] );

  if ($row['description']) {
    # clean up description
    $description = $row['description'];
    $description = str_replace("\\", "\\\\", $description);
    $description = str_replace("\"", "\\\"", $description);
    echo data_triple( $assay, $ONTO . "hasDescription", $description );
  }
  if ($row['doc_id']) {
    $docProps = mysql_query("SELECT DISTINCT chembl_id FROM docs WHERE doc_id = " . $row['doc_id']);
    while ($docProp = mysql_fetch_assoc($docProps)) {
      echo triple( $assay, $CITO . "citesAsDataSource", $CHEMBL . $docProp['chembl_id'] );
    }
  }

  $props = mysql_query("SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysql_fetch_assoc($props)) {
    if ($prop['tid']) {
      $targetURI = $TRG . "t" . $prop['tid'];
      if ($prop['confidence_score']) {
        $targetScore = $assay . "/score/t" . $prop['tid'];
        echo triple( $assay, $ONTO . "hasTargetScore", $targetScore);
        echo triple( $targetScore, $ONTO . "forTarget", $targetURI);
        echo typeddata_triple( $targetScore, $ONTO . "hasRelationshipType", $prop['relationship_type'], $XSD . "string" );
        echo typeddata_triple( $targetScore, $ONTO . "isComplex", $prop['complex'], $XSD . "int" );
        echo typeddata_triple( $targetScore, $ONTO . "isMulti", $prop['multi'], $XSD . "int" );
      }
    }
  }
  if ($row['assay_type']) {
    $props = mysql_query("SELECT DISTINCT * FROM assay_type WHERE assay_type = " . $row['assay_type']);
    while ($prop = mysql_fetch_assoc($props)) {
      echo triple( $assay, $ONTO . "hasAssayType", $ONTO . $props['assay_desc'] );
    }
  }
}

?>
