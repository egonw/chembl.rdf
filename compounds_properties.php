<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysqli_connect("localhost", $user, $pwd) or die(mysqli_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysqli_select_db($db) or die(mysqli_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysqli_query(
  "SELECT * FROM compound_properties " . $limit
);

$num = mysqli_numrows($allIDs);

# CHEMINF mappings
$descs = array(
  "alogp" => "CHEMINF_000305",
  "hba" => "CHEMINF_000309",
  "hbd" => "CHEMINF_000310",
  "psa" => "CHEMINF_000308",
  "rtb" => "CHEMINF_000311",
  "acd_most_apka" => "CHEMINF_000324",
  "acd_most_bpka" => "CHEMINF_000325",
  "acd_logp" => "CHEMINF_000321",
  "acd_logd" => "CHEMINF_000323",
  "num_ro5_violations" => "CHEMINF_000314",
  "ro3_pass" => "CHEMINF_000317",
  "med_chem_friendly" => "CHEMINF_000319",
  "full_mwt" => "CHEMINF_000198",
  "mw_freebase" => "CHEMINF_000350",
);
$descTypes = array(
  "alogp" => "double",
  "hba" => "nonNegativeInteger",
  "hbd" => "nonNegativeInteger",
  "psa" => "double",
  "rtb" => "nonNegativeInteger",
  "acd_most_apka" => "double",
  "acd_most_bpka" => "double",
  "acd_logp" => "double",
  "acd_logd" => "double",
  "num_ro5_violations" => "nonNegativeInteger",
  "ro3_pass" => "string",
  "med_chem_friendly" => "string",
  "full_mwt" => "double",
  "mw_freebase" => "double",
);

while ($row = mysqli_fetch_assoc($allIDs)) {
  $molregno = $row['molregno'];
  $molecule = $MOL . "m" . $molregno;

  foreach ($descs as $value => $cheminf) {
    if ($row[$value]) {
      $molprop = $molecule. "/$value";
      echo triple($molecule, $CHEMINF . "CHEMINF_000200", $molprop);
      echo triple($molprop, $RDF . "type", $CHEMINF . "$cheminf");
      echo typeddata_triple($molprop, $CHEMINF . "SIO_000300", $row[$value], $XSD . $descTypes[$value] );
    }
  }
}

?>
