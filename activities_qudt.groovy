import groovy.sql.Sql
import org.openrdf.repository.Repository
import org.openrdf.repository.sail.SailRepository
import org.openrdf.sail.memory.MemoryStore
import org.openrdf.model.vocabulary.RDFS
import org.openrdf.model.vocabulary.RDF
import org.openrdf.rio.ntriples.NTriplesWriter
import com.github.jqudt.Quantity
import com.github.jqudt.onto.UnitFactory

// export CLASSPATH=$(JARS=(*.jar); IFS=:; echo "${JARS[*]}")

def props = new Properties()
new File("vars.properties").withInputStream { stream -> props.load(stream) }

def url = "jdbc:mysql://localhost/" + props.dbprefix + props.version
def sql = Sql.newInstance(url, props.user, props.pwd, "com.mysql.jdbc.Driver")

allMolregno = "SELECT DISTINCT * FROM activities " + props.limit

unitMappings = [
  nM:"http://www.openphacts.org/units/Nanomolar",
  uM:"http://www.openphacts.org/units/Micromolar",
  mM:"http://www.openphacts.org/units/Millimolar",
  pM:"http://www.openphacts.org/units/Picomolar",
  "%":"http://qudt.org/schema/qudt#floatPercentage",
  "ug.mL-1":"http://www.openphacts.org/units/MicrogramPerMilliliter",
  "ug/ml":"http://www.openphacts.org/units/MicrogramPerMilliliter",
  "ug ml-1":"http://www.openphacts.org/units/MicrogramPerMilliliter",
  "pg ml-1":"http://www.openphacts.org/units/PicogramPerMilliliter",
]

normalizationMappings = [
  "IC50" : [
    nM:"nM",
    uM:"nM",
    mM:"nM",
    pM:"nM",
    "ug.mL-1":"ug/ml",
    "ug/ml":"ug/ml",
    "ug ml-1":"ug/ml",
    "pg ml-1":"ug/ml",
    "%":"%"
  ],
  "Potency" : [
    M:"uM",
    uM:"uM",
    nM:"uM",
    mM:"uM",
    "%":"%"
  ]
]

ACT = props.rooturi + "activity/"
RES = props.rooturi + "resource/"
ONTO = "http://rdf.farmbio.uu.se/chembl/onto/#"
CITO = "http://purl.org/spar/cito/"
MOL = props.rooturi + "molecule/"
CHEMBL = props.rooturi + "chemblid/"
ASS = props.rooturi + "assay/"
OPS = "http://www.openphacts.org/chembl/onto/#"

unitFactory = UnitFactory.getInstance();

sql.eachRow(allMolregno) { row ->
  def repos = new SailRepository(new MemoryStore())
  repos.initialize()
  con = repos.getConnection();
  factory = repos.getValueFactory();

  actURI = factory.createURI(ACT + "a" + row.activity_id)

  if (row.standard_value) {
    type = row.standard_type

    // now do the units: check if we need to use QUDT and if we normalize
    units = row.standard_units
    if (units != null && unitMappings.containsKey(units)) {
      qudtUnits = unitMappings.get(units)

      // first output the value as QUDT
      con.add(actURI,
        factory.createURI(OPS + "standardValue"),
        factory.createLiteral((double)row.standard_value)
      )
      con.add(actURI,
        factory.createURI(OPS + "standardUnit"),
        factory.createURI(qudtUnits)
      )

      // now see if I can normalize things
      originalUnit = unitFactory.getUnit(qudtUnits)
      if (originalUnit != null) {
        if (originalUnit.abbreviation == null) originalUnit.abbreviation = units
        quantity = new Quantity(row.standard_value, originalUnit);

        if (normalizationMappings.containsKey(type)) {
          // normalize the value, if we know how
          if (normalizationMappings[type] != null && normalizationMappings[type][units] != null) {
            normalizedUnit = unitFactory.getUnit(unitMappings[normalizationMappings[type][units]])
            if (normalizedUnit != null & normalizedUnit != originalUnit) {
              normalizedQuantity = quantity.convertTo(normalizedUnit)
              con.add(actURI,
                factory.createURI(OPS + "normalisedValue"),
                factory.createLiteral(normalizedQuantity.value)
              )
              con.add(actURI,
                factory.createURI(OPS + "normalisedUnit"),
                factory.createURI(normalizedUnit.resource.toString())
              )
            }
          } else {
            println "# WARN: wanting to normalize $type $units, but no normalization found: " + normalizationMappings[type]
          }
        }
      }
    }
  }

  con.export(new NTriplesWriter(System.out))
  con.close()
  repos.shutDown()
}
