# About

ChEMBL is medicinal chemistry database by the team of dr. J. Overington at the EBI.

  http://www.ebi.ac.uk/chembl/

It is detailed in this paper (doi:10.1093/nar/gkr777):

  http://nar.oxfordjournals.org/content/early/2011/09/22/nar.gkr777.short

This project develops, releases, and hosts a RDF version of ChEMBL, independent from
the ChEMBL team who make their own RDF version.

The main SPARQL end point is available from Uppsala University at:

  http://rdf.farmbio.uu.se/chembl/sparql

Or as SNORQL at:

  http://rdf.farmbio.uu.se/chembl/snorql

Additionally, the data is available as Linked Data via LinkedChemistry.info:

  http://linkedchemistry.info

# Citation

If you use this (3rd party) RDF version of ChEMBL, please cite this paper. It is currently under review:

E.L. Willighagen, A. Waagmeester, O. Spjuth, P. Ansell, A.J. Williams, V. Tkachenko,
J. Hastings, B. Chen, D.J. Wild, The ChEMBL database as Linked Open Data,
2013, J. Cheminformatics, 2013, 5:23 doi:10.1186/1758-2946-5-23, http://www.jcheminf.com/content/5/1/23/abstract

# Download

Alternatively, you can download the full set of triples as n3 or n-triples from:

  http://semantics.bigcat.unimaas.nl/chembl/

# Copyright / License

The ChEMBL database is copyrighted by John Overington et al., and licensed CC-BY-SA:

  http://creativecommons.org/licenses/by-sa/3.0/

as explained on:

  http://www.ebi.ac.uk/chembldb/
  ftp://ftp.ebi.ac.uk/pub/databases/chembl/ChEMBLdb/releases/chembl_13/LICENSE

The ChEMBL FAQ explains how you can fullfil the attribution part of the license:

  https://www.ebi.ac.uk/chembldb/index.php/faq#faq29

If you use this RDF version of ChEMBL, you should cite this paper, pending
a more dedicated paper, where the RDF version of ChEMBL has been used and
demonstrated:

  http://www.jbiomedsem.com/content/2/S1/S6

Authors that contributed (see also the Git commit history) are:

  Egon Willighagen, Peter Ansell

These scripts were tested against version 13 of ChEMBL, as downloaded from:

  ftp://ftp.ebi.ac.uk/pub/databases/chembl/releases/chembl_13/

# Requirements

ChEMBL 13, OpenRDF (aka Sesame), SLF4J, and the MySQL JDBC plugin.

# Installation

For PHP the MySQL server user and password are configured in a file called like
mysqli.ini, for example on Debian:

    /etc/php5/conf.d/20-mysqli.ini

The scripts expect a script only readble by the server software called vars.properties, with content like:

    <?php

    $version = '13';
    $rooturi = 'http://data.kasabi.com/dataset/chembl-rdf/' . $version . '/';

    $db = 'chembl_' . $version;
    $user = 'user';
    $pwd = 'secret';

    // use the next line to limit the output 
    // $limit = ' LIMIT 5';
    $limit = '';

    ?>

to access the MySQL database with the ChEMBL content.
