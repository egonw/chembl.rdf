<?php header('Content-type: text/n3'); ?>

<?php 

include 'namespaces.php';
include 'functions.php';

$ini = parse_ini_file("vars.properties");
$rooturi = $ini["rooturi"];
$importedBy = $ini["importedBy"];
$db = $ini["dbprefix"] . $ini["version"];

$con = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), $db);
if (mysqli_connect_errno($con)) die(mysqli_connect_errno($con));

# VOID
$mastervoid = $rooturi . "void.ttl#";
$masterset = $mastervoid . "ChEMBLRDF";
$thisset = $mastervoid . "ChEMBLAssay";
$thisSetTitle = "ChEMBL Assay";
$thisSetDescription = "Assay information from ChEMBL.";

$current_date = gmDate("Y-m-d\TH:i:s");
echo triple( $thisset , $PAV . "createdBy",  $importedBy );
echo typeddata_triple( $thisset, $PAV . "createdOn", $current_date,  $XSD . "dateTime" );
echo triple( $thisset , $PAV . "authoredBy",  $importedBy );
echo typeddata_triple( $thisset, $PAV . "authoredOn", $current_date,  $XSD . "dateTime" );
echo triple( $thisset, $RDF . "type", $VOID . "Dataset" );
echo triple( $masterset, $VOID . "subset" , $thisset );

echo data_triple( $thisset, $DCT . "title", $thisSetTitle ) ;
echo data_triple( $thisset, $DCT . "description", $thisSetDescription ) ;
echo triple( $thisset, $DCT . "license", $ini["license"] ) ;
echo "\n";

$allIDs = mysqli_query($con,
    "SELECT DISTINCT * FROM assays " . $ini["limit"]
);

while ($row = mysqli_fetch_assoc($allIDs)) {
  $assay = $CHEMBL . $row['chembl_id'];
  echo triple( $assay, $RDF . "type", $ONTO . "Assay" );

  echo data_triple( $assay, $RDFS . "label", $row['chembl_id'] );
  $chemblChemInfRes = $assay . "/chemblid";
  echo triple($assay, $CHEMINF . "CHEMINF_000200", $chemblChemInfRes);
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
    $docProps = mysqli_query($con, "SELECT DISTINCT chembl_id FROM docs WHERE doc_id = " . $row['doc_id']);
    while ($docProp = mysqli_fetch_assoc($docProps)) {
      echo triple( $assay, $CITO . "citesAsDataSource", $CHEMBL . $docProp['chembl_id'] );
    }
  }

  $props = mysqli_query($con, "SELECT DISTINCT * FROM assay2target WHERE assay_id = " . $row['assay_id']);
  while ($prop = mysqli_fetch_assoc($props)) {
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
    $props = mysqli_query($con, "SELECT DISTINCT * FROM assay_type WHERE assay_type = '" . $row['assay_type'] . "'");
    while ($prop = mysqli_fetch_assoc($props)) {
      echo triple( $assay, $ONTO . "hasAssayType", $ONTO . $prop['assay_desc'] );
    }
  }
}

?>
