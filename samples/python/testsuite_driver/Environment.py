from saxoncee import *
import os

optimizer_flags = '-'

class Environment:




    def __init__(self):
        ...
        self.proc = PySaxonProcessor(license=True)
        '''self.proc.set_configuration_property("http://saxon.sf.net/feature/licenseFileLocation",
                                               "/Users/ond1/work/development/git/private/saxon-license.lic")'''
        if os.getenv("SAXON_LICENSE_DIR") is not None:
            self.proc.set_configuration_property("http://saxon.sf.net/feature/licenseFileLocation",
                                                   os.getenv("SAXON_LICENSE_DIR") + "/saxon-license.lic")

        self.lang = None
        self.proc.set_configuration_property('http://saxon.sf.net/feature/optimizationLevel', optimizer_flags)

        self.xpath_proc = self.proc.new_xpath_processor()
        self.base_uri = ""
        self.spec = None
        self.xquery_proc = self.proc.new_xquery_processor()

        self.xslt_proc = self.proc.new_xslt30_processor()
        self.sheet = None

        self.context_item = None

        self.source_docs = {}








