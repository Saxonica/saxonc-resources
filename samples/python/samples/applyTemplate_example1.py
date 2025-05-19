from saxonche import *
import pathlib

saxonproc = PySaxonProcessor()
trans = saxonproc.new_xslt30_processor()
source = "<?xml version='1.0'?>  <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'  xmlns:xs='http://www.w3.org/2001/XMLSchema'  version='3.0'>  <xsl:output method='text' item-separator='~~'/>  <xsl:template match='.'>     <xsl:param name='a' as='xs:double'/>     <xsl:param name='b' as='xs:float'/>     <xsl:sequence select='., $a + $b'/>  </xsl:template>  </xsl:stylesheet>"
executable = trans.compile_stylesheet(stylesheet_text=source)
executable.set_property("!omit-xml-declaration", "yes")
executable.set_result_as_raw_value(True)
paramArr = {"a":saxonproc.make_integer_value(12), "b":saxonproc.make_integer_value(5)}

executable.set_initial_template_parameters(False, paramArr)

executable.set_initial_match_selection(xdm_value=saxonproc.make_integer_value(16))

sw = executable.apply_templates_returning_string()

print(sw)
