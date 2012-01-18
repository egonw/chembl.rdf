<?php

function triple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> <" . $object . "> .\n";
}
function data_triple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\" .\n";
}
function typeddata_triple($subject, $predicate, $object, $type) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\"^^<" . $type . "> .\n";
}

?>
