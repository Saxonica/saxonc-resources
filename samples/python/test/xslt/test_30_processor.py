import pytest
import saxon_import
saxonc = saxon_import.import_star(globals())

class Test30Processor:
    saxon_processor = PySaxonProcessor()

    @pytest.fixture
    def processor(self):
        return self.saxon_processor.new_xslt30_processor()

    def test_get_parameter(self, processor):
        value = self.saxon_processor.make_string_value("the value")
        processor.set_parameter("the-param", value)

        retrieved_param = processor.get_parameter("the-param")
        assert value.string_value == retrieved_param.string_value
