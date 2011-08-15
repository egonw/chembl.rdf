<?php

function triple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> <" . $object . "> .\n";
}
function dataTriple($subject, $predicate, $object) {
  return "<" . $subject . "> <" . $predicate . "> \"" . $object . "\" .\n";
}

?>
