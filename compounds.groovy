import groovy.sql.Sql
import org.openrdf.repository.Repository
import org.openrdf.repository.sail.SailRepository
import org.openrdf.sail.memory.MemoryStore
import org.openrdf.model.vocabulary.RDFS
import org.openrdf.model.vocabulary.RDF
import org.openrdf.rio.ntriples.NTriplesWriter

// export CLASSPATH=$(JARS=(*.jar); IFS=:; echo "${JARS[*]}")

def props = new Properties()
new File("vars.properties").withInputStream { stream -> props.load(stream) }

def url = "jdbc:mysql://localhost/" + props.dbprefix + props.version
def sql = Sql.newInstance(url, props.user, props.pwd, "com.mysql.jdbc.Driver")

allMolregno = "SELECT DISTINCT molregno, chembl_id FROM molecule_dictionary " + props.limit

CHEMBL = props.rooturi + "chemblid/";
xsdStringURI = "http://www.w3.org/2001/XMLSchema#string"

sql.eachRow(allMolregno) { row ->
  def repos = new SailRepository(new MemoryStore())
  repos.initialize()
  con = repos.getConnection();
  factory = repos.getValueFactory();

  molURI = factory.createURI(CHEMBL + row.chembl_id)

  // the names
  allNames = "SELECT DISTINCT compound_name FROM compound_records WHERE molregno = " + row.molregno
  sql.eachRow(allNames) { nameRow ->
    if (nameRow['compound_name'] != null) {
      con.add(molURI, RDFS.LABEL,
        factory.createLiteral(nameRow['compound_name'], factory.createURI(xsdStringURI))
      )
    }
  }

  // the synonyms
  allNames = "SELECT DISTINCT synonyms FROM molecule_synonyms WHERE molregno = " + row.molregno
  sql.eachRow(allNames) { nameRow ->
    if (nameRow['synonyms'] != null) {
      con.add(molURI, RDFS.LABEL, nameRow['synonyms'],
        factory.createLiteral(nameRow['compound_name'], factory.createURI(xsdStringURI))
      )
    }
  }

  con.export(new NTriplesWriter(System.out))
  con.close()
}
