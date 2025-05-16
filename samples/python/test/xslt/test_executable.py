import pytest
import saxon_import
saxonc = saxon_import.import_star(globals())
import gc

class TestExecutable:
    saxon_processor = PySaxonProcessor()
    xslt = """<xsl:stylesheet version="3.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema">
            <xsl:output method="text"/>
            <xsl:template name="go">
                <xsl:param name="a" select="'a'" as="xs:string"/>
                <xsl:param name="b" select="'b'" as="xs:string"/>
                <xsl:value-of select="concat($a, $b)"/>
                <xsl:call-template name="gone"/>
            </xsl:template>
            
            <xsl:template name="gone">
                <xsl:param name="a" select="'A'" tunnel="yes" as="xs:string"/>
                <xsl:param name="b" select="'B'" tunnel="yes" as="xs:string"/>
                <xsl:value-of select="concat($a, $b)"/>
            </xsl:template>
        </xsl:stylesheet>
        """

    @pytest.fixture
    def processor(self):
        return self.saxon_processor.new_xslt30_processor()

    @pytest.fixture
    def executable(self, processor):
        return processor.compile_stylesheet(stylesheet_text=self.xslt)

    def test_set_initial_template_parameters(self, executable):
        """Initial non-tunneling template parameters can be set"""
        value = self.saxon_processor.make_string_value("1")
        executable.set_initial_template_parameters(False, {"a": value})

        result = executable.call_template_returning_string("go")

        assert result == "1bAB"

    def test_set_initial_tunneling_template_parameters(self, executable):
        """Initial tunneling template parameters can be set"""
        value = self.saxon_processor.make_string_value("1")
        executable.set_initial_template_parameters(True, {"a": value})

        result = executable.call_template_returning_string("go")

        assert result == "ab1B"

    def test_set_all_initial_template_parameters(self, executable):
        """Initial tunneling and non-tunneling parameters can be set"""
        v = self.saxon_processor.make_string_value
        executable.set_initial_template_parameters(True, {"a": v("!")})
        executable.set_initial_template_parameters(False, {"a": v("1")})

        result = executable.call_template_returning_string("go")

        assert result == "1b!B"

    def test_set_initial_template_parameters_again(self, executable):
        """Calling set_initial_template_parameters more than once is additive"""
        v = self.saxon_processor.make_string_value
        p1 = v("1")
        p2 = v("2")
        executable.set_initial_template_parameters(False, {"a": p1})
        executable.set_initial_template_parameters(False, {"b": p2})

        actual = executable.get_initial_template_parameters(False)

        # We can't safely perform equality operations on XDM Values, hence this:
        assert {k : str(v) for k, v in actual.items()} == {"a": "1", "b": "2"}
