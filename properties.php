<?php header('Content-type: text/n3');

include 'vars.php';
include 'namespaces.php';
include 'functions.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db($db) or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$allIDs = mysql_query(
  "SELECT * FROM compound_properties " . $limit
);

$num = mysql_numrows($allIDs);

# CHEMINF mappings
$descs = array(
  "alogp" => "CHEMINF_000305",
);
$descTypes = array(
  "alogp" => "double",
);

while ($row = mysql_fetch_assoc($allIDs)) {
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
