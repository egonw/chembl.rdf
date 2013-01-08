import groovy.sql.Sql
import org.openrdf.repository.Repository
import org.openrdf.repository.sail.SailRepository
import org.openrdf.sail.memory.MemoryStore
import org.openrdf.model.vocabulary.RDFS
import org.openrdf.model.vocabulary.RDF
import org.openrdf.model.vocabulary.XMLSchema
import org.openrdf.rio.ntriples.NTriplesWriter
import org.codehaus.groovy.runtime.DateGroovyMethods;
import java.util.Date;

// export CLASSPATH=$(JARS=(*.jar); IFS=:; echo "${JARS[*]}")

xsdStringURI = "http://www.w3.org/2001/XMLSchema#string"

def props = new Properties()
new File("vars.properties").withInputStream { stream -> props.load(stream) }
if (props.mysqliini) new File(props.mysqliini).withInputStream { stream -> props.load(stream) }

def url = "jdbc:mysql://localhost/" + props.dbprefix + props.version
def sql = Sql.newInstance(url, props["mysqli.default_user"], props["mysqli.default_pw"], "com.mysql.jdbc.Driver")

// VOID
PAV = "http://purl.org/pav/";
DUL = "http://www.ontologydesignpatterns.org/ont/dul/DUL.owl#";
VOID = "http://rdfs.org/ns/void#";
DCT = "http://purl.org/dc/terms/";

rooturi = props["rooturi"]
importedBy = props["importedBy"]
mastervoid = rooturi + "void.ttl#";
masterset = mastervoid + "ChEMBLRDF";
thisset = mastervoid + "ChEMBLTarget";
thisSetTitle = "ChEMBL Target";
thisSetDescription = "Target information from ChEMBL.";

def repos = new SailRepository(new MemoryStore())
repos.initialize()
con = repos.getConnection();
factory = repos.getValueFactory();

// actURI = factory.createURI(ACT + "a" + row.activity_id)
// con.add(actURI, RDF.TYPE, factory.createURI(ONTO + "Activity"))

dateTime = new Date()
current_date = DateGroovyMethods.format(dateTime, "yyyy-MM-dd'\\T'HH:mm:ss");
thissetURI = factory.createURI(thisset)
con.add( thissetURI, factory.createURI(PAV + "createdBy"),
  factory.createLiteral(importedBy,  XMLSchema.STRING));
con.add( thissetURI, factory.createURI(PAV + "createdOn"), 
  factory.createLiteral(current_date,  XMLSchema.DATETIME));
con.add( thissetURI, factory.createURI(PAV + "authoredBy"),
  factory.createLiteral(importedBy,  XMLSchema.STRING));
con.add( thissetURI, factory.createURI(PAV + "authoredOn"), 
  factory.createLiteral(current_date,  XMLSchema.DATETIME));
con.add( thissetURI, RDF.TYPE, factory.createURI(VOID + "Dataset")) 
con.add( thissetURI, RDF.TYPE, factory.createURI(VOID + "Dataset")) 
con.add( factory.createURI(masterset), factory.createURI(VOID + "subset") , thissetURI );

con.add( thissetURI, RDF.TYPE, factory.createLiteral(thisSetTitle, XMLSchema.STRING)) 
con.add( thissetURI, factory.createURI(DCT + "description"), factory.createLiteral(thisSetDescription, XMLSchema.STRING)) 
con.add( thissetURI, factory.createURI(DCT + "license"), factory.createURI(props["license"])) 

con.export(new NTriplesWriter(System.out))
con.close()
repos.shutDown()
println ""

allMolregno = "SELECT DISTINCT * FROM activities WHERE activity_id = 1407742 " + props.limit

ACT = props.rooturi + "activity/"
RES = props.rooturi + "resource/"
ONTO = "http://rdf.farmbio.uu.se/chembl/onto/#"
CITO = "http://purl.org/spar/cito/"
MOL = props.rooturi + "molecule/"
CHEMBL = props.rooturi + "chemblid/"
ASS = props.rooturi + "assay/"

sql.eachRow(allMolregno) { row ->
  repos = new SailRepository(new MemoryStore())
  repos.initialize()
  con = repos.getConnection();
  factory = repos.getValueFactory();

  actURI = factory.createURI(ACT + "a" + row.activity_id)
  con.add(actURI, RDF.TYPE, factory.createURI(ONTO + "Activity"))

  // OK, we have to do some magic now, and resolve the CHEMBLxxx ids
  assayCHEMBLid = "SELECT DISTINCT chembl_id FROM assays WHERE assay_id = " + row.assay_id
  sql.eachRow(assayCHEMBLid) { assayRow ->
    con.add(actURI, factory.createURI(ONTO + "onAssay"), factory.createURI(CHEMBL + assayRow.chembl_id))
  }
  molCHEMBLid = "SELECT DISTINCT chembl_id FROM molecule_dictionary WHERE molregno = " + row.molregno
  sql.eachRow(molCHEMBLid) { molRow ->
    con.add(actURI, factory.createURI(ONTO + "forMolecule"), factory.createURI(CHEMBL + molRow.chembl_id))
  }

  if (row.doc_id) {
    docCHEMBLid = "SELECT DISTINCT chembl_id FROM docs WHERE doc_id = " + row.doc_id
    sql.eachRow(docCHEMBLid) { docRow ->
      con.add(actURI, factory.createURI(CITO + "citesAsDataSource"), factory.createURI(CHEMBL + docRow.chembl_id))
    }
  }
  if (row.relation)
    con.add(actURI, factory.createURI(ONTO + "relation"), factory.createLiteral(row.relation, factory.createURI(xsdStringURI)))

  if (row.standard_value) {
    type = row.standard_type
    con.add(actURI, factory.createURI(ONTO + "type"), factory.createLiteral(type, factory.createURI(xsdStringURI)))

    // now do the units: check if we need to use QUDT and if we normalize
    units = row.standard_units
    // use the old approach
    con.add(actURI, factory.createURI(ONTO + "standardValue"), factory.createLiteral((float)row.standard_value))
    if (units != null) {
      // units are sometimes null, but the value should always be given
      con.add(actURI, factory.createURI(ONTO + "standardUnits"), factory.createLiteral(units, factory.createURI(xsdStringURI)))
    }
  }

  con.export(new NTriplesWriter(System.out))
  con.close()
  repos.shutDown()
}
