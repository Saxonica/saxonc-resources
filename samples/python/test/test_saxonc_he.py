import pytest
import saxonche

@pytest.fixture
def saxonproc():
    return saxonche.PySaxonProcessor()

def test_version(saxonproc):
    """SaxonProcessor version string content"""
    ver = saxonproc.version

    assert ver.startswith('SaxonC-HE')
    assert ver.endswith('from Saxonica')

def test_saxon_edition(saxonproc):
    assert saxonproc.edition == 'he'

def test_failure():
    assert False