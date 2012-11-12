all: assays activities compounds targets docs

assays: assays.nt
activities: activities.nt activities_qudt.nt
compounds: compounds.nt compounds_labels.nt compounds_ls_ebi.nt
targets: targets.nt targets_ls_bio2rdf.nt targets_ls_uniprot.nt
docs: docs.nt docs_ls_crossref.nt docs_ls_pubmed.nt

clean:
	@rm compounds*.nt assays*.nt activities*.nt

assays.nt: assays.php
	@php assays.php > assays.nt

activities.nt: activities.groovy
	@groovy activities.groovy > activities.nt

activities_qudt.nt: activities_qudt.groovy
	@groovy activities_qudt.groovy > activities_qudt.nt

compounds.nt: compounds.php
	@php compounds.php > compounds.nt

compounds_labels.nt: compounds_labels.groovy
	@groovy compounds_labels.groovy > compounds_labels.nt

compounds_ls_ebi.nt: compounds_ls_ebi.php
	@php compounds_ls_ebi.php > compounds_ls_ebi.nt

targets.nt: targets.php
	@php targets.php > targets.nt

targets_ls_bio2rdf.nt: targets_ls_bio2rdf.php
	@php targets_ls_bio2rdf.php > targets_ls_bio2rdf.nt

targets_ls_uniprot.nt: targets_ls_uniprot.php
	@php targets_ls_uniprot.php > targets_ls_uniprot.nt

docs.nt: docs.php
	@php docs.php > docs.nt

docs_ls_crossref.nt: docs_ls_crossref.php
	@php docs_ls_crossref.php > docs_ls_crossref.nt

docs_ls_pubmed.nt: docs_ls_pubmed.php
	@php docs_ls_pubmed.php > docs_ls_pubmed.nt
