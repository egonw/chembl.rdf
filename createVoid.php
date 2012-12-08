<?php
  $ini = parse_ini_file("vars.properties");
  $rooturi = $ini["rooturi"];
  $version = $ini["version"];
  $subversion = $ini["subversion"];
  $importedOn = $ini["importedOn"];
  $importedBy = $ini["importedBy"];
  $datadump = $ini["datadump"];
?>
@prefix void: <http://rdfs.org/ns/void#> .
@prefix voag: <http://voag.linkedmodel.org/voag#> .
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

:ChEMBL foaf:homepage <https://www.ebi.ac.uk/chembl/> ;
  dcterms:title "ChEMBL" .

:ChEMBLRDF a void:Dataset ;
  foaf:homepage <https://github.com/egonw/chembl.rdf> ;
  dcterms:title "ChEMBL-RDF" ;
  dcterms:description "RDF data extracted from ChEMBL, a CC-BY-SA database developed at the EBI by J. Overington et al." ;
  pav:derivedFrom :ChEMBL ;
  dcterms:subject <http://live.dbpedia.org/resource/Pharmacology> ;
<?php
  $current_date = gmDate("Y-m-d\TH:i:s");
  print("  pav:createdBy <" . $importedBy . "> ;\n");
  print("  pav:createdOn \"" . $current_date . "\"^^xsd:dateTime ;\n");
  print("  pav:createdWith <https://raw.github.com/openphacts/chembl.rdf/master/createVoid.php> ;\n");
  // print("  dcterms:modified \"" . "\" ;\n");
  print("  void:uriSpace \"" . $rooturi . "\" ;\n");
  print("  pav:version \"" . $version . "_" . $subversion . "\" ; \n");
  print("  pav:importedOn \"" . $importedOn . "\"^^xsd:dateTime ; \n");
  print("  pav:importedBy <" . $importedBy . "> ; \n");
  print("  pav:importedFrom <ftp://ftp.ebi.ac.uk/pub/databases/chembl/ChEMBLdb/releases/chembl_" . $version . "/> ; \n");
  print("  void:dataDump <" . $datadump . "> ;\n");
  print("  void:exampleResource <" . $rooturi .  "chemblid/CHEMBL146554> ;\n");
?>
  voag:frequencyOfChange voag:UncertainFrequency ;
  void:vocabulary <http://xmlns.com/foaf/0.1/> ;
  void:vocabulary <http://purl.org/dc/terms/>;
  void:vocabulary <http://purl.org/spar/cito/> ;
  void:vocabulary <http://semanticscience.org/resource/> ;
  dcterms:license <http://creativecommons.org/licenses/by-sa/3.0/> .

<> a void:DatasetDescription;
  dcterms:title "ChEMBL-RDF dataset VoID description" ;
  dcterms:description "A VoID description of the ChEMBL-RDF dataset." ;
<?php
  print("  pav:createdOn \"" . $current_date . "\"^^xsd:dateTime ;\n");
  print("  pav:lastUpdateOn \"" . $current_date . "\"^^xsd:dateTime ;\n");
  print("  pav:createdBy <" . $importedBy . "> ;\n");
?>
  foaf:primaryTopic :ChEMBLRDF .
