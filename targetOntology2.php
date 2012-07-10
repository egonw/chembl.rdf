<?php header('Content-type: application/rdf+xml'); ?>

<?php

include 'vars.php';
include 'namespaces.php';
include 'functions.php';
include 'to.php';

# classifications
foreach ($array as $code => $array2) {
    $higher = $array2["higher"];
    $uri = $array2["uri"];
    $desc = $array2["label"];
    $example = $array2["classification"];
    echo triple($uri, $RDF . "type", $SKOS . "Concept");
    echo triple($uri, $RDFS . "subClassOf", $higher);
    echo triple($higher, $SKOS . "narrower", $uri);
    echo data_triple($uri, $SKOS . "prefLabel", $desc);
    echo data_triple($uri, $SKOS . "scopeNote", $code);
    echo data_triple($uri, $SKOS . "example", $example);
}

?>
