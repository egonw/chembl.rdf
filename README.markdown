# About

ChEMBL is medicinal chemistry database by the team of dr. J. Overington at the EBI.

  http://www.ebi.ac.uk/chembl/

The data is copyrighted by them, and licensed CC-BY-SA:

  http://creativecommons.org/licenses/by-sa/3.0/

as explained on:

  http://www.ebi.ac.uk/chembldb/
  ftp://ftp.ebi.ac.uk/pub/databases/chembl/releases/chembl_06/LICENSE

These scripts were tested against version 06 of ChEMBL, as downloaded from:

  ftp://ftp.ebi.ac.uk/pub/databases/chembl/releases/chembl_06/

# Requirements

ChEMBL 06.

# Installation

The scripts expect a script only readble by the server software called vars.php, with content like:

<?php

$db = 'chembl_06';
$user = 'user';
$pwd = 'secret';

?>

to access the MySQL database with the ChEMBL content.
