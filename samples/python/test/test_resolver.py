import pytest
import saxon_import
saxonc = saxon_import.import_star(globals())

@pytest.fixture
def proc():
    return PySaxonProcessor()

def test_resolver_cache(proc):
    """The XML Resolver's local caches of common DTDs and resources should be usable
    """
    xpath = proc.new_xpath_processor()
    result = xpath.evaluate("unparsed-text('https://xmlresolver.org/data/resolver/succeeded/test/check.xml')")
    assert isinstance(result, PyXdmValue)
