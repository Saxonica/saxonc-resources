import pytest
import saxon_import
saxonc = saxon_import.import_star(globals())

@pytest.fixture
def proc():
    return PySaxonProcessor()

def test_get_empty_sequence(proc):
    empty = proc.empty_sequence()
    assert isinstance(empty, PyXdmValue)
    assert empty.size == 0
