<?php include 'vars.php'; ?>
@prefix void: <http://rdfs.org/ns/void#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix dcterms: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix wv: <http://vocab.org/waiver/terms/norms> .
@prefix sd: <http://www.w3.org/ns/sparql-service-description#> .
@prefix pav: <http://purl.org/pav/> .

<?php print("@prefix : <" . $rooturi . "void.ttl#> .\n"); ?>

:ChEMBLRDF a void:Dataset ;
  foaf:homepage <https://github.com/egonw/chembl.rdf> ;
  dcterms:title "ChEMBL-RDF" ;
  dcterms:description "RDF data extracted from ChEMBL, a CC-BY-SA database developed at the EBI by J. Overington et al." ;
<?php
  print("  dcterms:publisher \"" . $importedBy . "\" ;\n");
  print("  dcterms:created \"" . "\" ;\n");
  print("  dcterms:modified \"" . "\" ;\n");
  print("  void:uriSpace \"" . $rooturi . "\" ;\n");
  print("  pav:version \"" . $version . "_" . $subversion . "\" ; \n");
  print("  pav:importedOn \"" . $importedOn . "\" ; \n");
  print("  pav:importedBy \"" . $importedBy . "\" ; \n");
  print("  pav:importedFrom \"ftp://ftp.ebi.ac.uk/pub/databases/chembl/ChEMBLdb/releases/chembl_" . $version . "/\" ; \n");
?>
  void:vocabulary <http://xmlns.com/foaf/0.1/> ;
  void:vocabulary <http://purl.org/dc/terms/>;
  void:vocabulary <http://purl.org/spar/cito/> ;
  void:vocabulary <http://semanticscience.org/resource/> ;
  dcterms:license <http://creativecommons.org/licenses/by-sa/3.0/> .

<> a void:DatasetDescription;
  dcterms:title "A VoID Description of the ChEMBL-RDF dataset" ;
  dcterms:creator <http://egonw.github.com/#me> ;
  foaf:primaryTopic :ChEMBLRDF .
