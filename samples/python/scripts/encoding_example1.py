from saxonche import *


saxon_proc = PySaxonProcessor(license=False)

xpath_proc = saxon_proc.new_xpath_processor()

'''xpath_proc.set_property("!method", "xml")'''

xml_node = saxon_proc.parse_xml(xml_text="<doc><person id='d1'>Tim Brown</person><person id='d2'>Alex Smith</person></doc>")
node = saxon_proc.parse_xml(xml_text="<doc><e>تيست</e></doc>", encoding="UTF8")
print("Test:")
print(node.get_string_value(encoding="UTF8"))
trans = saxon_proc.new_xslt30_processor()
executable = trans.compile_stylesheet(stylesheet_text="<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'><xsl:output encoding='UTF-8' /><xsl:template match='e'>تيست: <xsl:value-of select='.'/></xsl:template></xsl:>

eNode = node.children[0].children[0]
executable.set_global_context_item(xdm_item=node)
executable.set_initial_match_selection(xdm_value=eNode)
executable.set_property("!encoding", "UTF8")
executable.apply_templates_returning_file(output_file="output_encoding_test.xml")
result_val = executable.apply_templates_returning_value()
print(result_val.item_at(0).get_node_value().get_string_value(encoding="UTF8"))
result = executable.apply_templates_returning_string(encoding="UTF8")

print(result.encode("UTF-8").decode(encoding="UTF8", errors="strict"))


xpath_proc.set_context(xdm_item=xml_node)
results = xpath_proc.evaluate("//person")
print(type(results))
print(results)
