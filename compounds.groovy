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

allMolregno = "SELECT DISTINCT molregno FROM molecule_dictionary " + props.limit

sql.eachRow(allMolregno) { row ->
  def repos = new SailRepository(new MemoryStore())
  repos.initialize()
  con = repos.getConnection();
  factory = repos.getValueFactory();

  molURI = factory.createURI(props.rooturi . "molecule/m" + row.molregno)

  // the names
  allNames = "SELECT DISTINCT compound_name FROM compound_records WHERE molregno = " + row.molregno
  sql.eachRow(allNames) { nameRow ->
    if (nameRow['compound_name'] != null) {
      con.add(molURI, RDFS.LABEL, factory.createLiteral(nameRow['compound_name']))
    }
  }

  // the synonyms
  allNames = "SELECT DISTINCT synonyms FROM molecule_synonyms WHERE molregno = " + row.molregno
  sql.eachRow(allNames) { nameRow ->
    if (nameRow['synonyms'] != null) {
      con.add(molURI, RDFS.LABEL, factory.createLiteral(nameRow['synonyms']))
    }
  }

  con.export(new NTriplesWriter(System.out))
  con.close()
}
