from saxonche import *

saxon_proc  = PySaxonProcessor(license=False)
saxon_proc.set_configuration_property('http://saxon.sf.net/feature/standardErrorOutputFile', 'SaxonCHE12ErrorOutputFile-Test1.txt')
xslt30_processor = saxon_proc.new_xslt30_processor()

try:
    executable = xslt30_processor.compile_stylesheet(stylesheet_file='error.xsl')
except Exception as err:
    print("========== See exception:")
    print(err)
    print("Something went wrong")
