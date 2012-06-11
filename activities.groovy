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

allMolregno = "SELECT DISTINCT * FROM activities " + props.limit

ACT = props.rooturi + "activity/"
RES = props.rooturi + "resource/"
ONTO = "http://rdf.farmbio.uu.se/chembl/onto/#"
CITO = "http://purl.org/spar/cito/"
MOL = props.rooturi + "molecule/"
CHEMBL = props.rooturi + "chemblid/"
ASS = props.rooturi + "assay/"

sql.eachRow(allMolregno) { row ->
  def repos = new SailRepository(new MemoryStore())
  repos.initialize()
  con = repos.getConnection();
  factory = repos.getValueFactory();

  actURI = factory.createURI(ACT + "a" + row.activity_id)
  con.add(actURI, RDF.TYPE, factory.createURI(ONTO + "Activity"))
  con.add(actURI, factory.createURI(ONTO + "onAssay"), factory.createURI(ASS + "a" + row.assay_id))
  con.add(actURI, factory.createURI(ONTO + "forMolecule"), factory.createURI(MOL + "m" + row.molregno))

  if (row.doc_id)
    con.add(actURI, factory.createURI(CITO + "citesAsDataSource"), factory.createURI(RES + "r" + row.doc_id))
  if (row.relation)
    con.add(actURI, factory.createURI(ONTO + "relation"), factory.createLiteral(row.relation))

  if (row.standard_value) {
    type = row.standard_type
    con.add(actURI, factory.createURI(ONTO + "type"), factory.createLiteral(type))

    // now do the units: check if we need to use QUDT and if we normalize
    units = row.standard_units
    // use the old approach
    con.add(actURI, factory.createURI(ONTO + "standardValue"), factory.createLiteral((float)row.standard_value))
    if (units != null) {
      // units are sometimes null, but the value should always be given
      con.add(actURI, factory.createURI(ONTO + "standardUnits"), factory.createLiteral(units))
    }
  }

  con.export(new NTriplesWriter(System.out))
  con.close()
  repos.shutDown()
}
