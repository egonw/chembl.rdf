<?php

function triple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> <" . $object . "> .\n";
}
function data_triple($subject, $predicate, $object) {
  $XSD = "http://www.w3.org/2001/XMLSchema#";
  return typeddata_triple($subject, $predicate, $object, $XSD . "string");
}
function typeddata_triple($subject, $predicate, $object, $type) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\"^^<" . $type . "> .\n";
}

?>
