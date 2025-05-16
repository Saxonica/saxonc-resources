import pytest
import saxon_import
saxonc = saxon_import.import_star(globals())

@pytest.fixture
def proc():
    return PySaxonProcessor()

def test_fetch_system_function(proc):
    """Getting a system function returns a PyXdmFunctionItem"""
    sys_func = PyXdmFunctionItem.get_system_function(proc, '{http://www.w3.org/2005/xpath-functions}concat', 2)

    assert isinstance(sys_func, PyXdmFunctionItem)

def test_call_function(proc):
    """A system function that was fetched can be called"""
    sys_func = PyXdmFunctionItem.get_system_function(proc, '{http://www.w3.org/2005/xpath-functions}concat', 2)

    result = sys_func.call([proc.make_string_value("a"), proc.make_string_value("b")])

    assert result.size == 1
    assert result.head.is_atomic
    assert result.head.get_string_value() == "ab"
