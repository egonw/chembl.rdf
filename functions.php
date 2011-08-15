<?php

function triple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> <" . $object . "> .\n";
}
function dataTriple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\" .\n";
}
function typeddataTriple($subject, $predicate, $object, $type) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\"^^<" . $type . "> .\n";
}

?>
