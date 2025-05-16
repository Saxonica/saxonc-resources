import os
import importlib

def import_mod():
    """
    This replicates `import saxonche`
    :return: the saxonc module
    """
    return importlib.import_module("saxonc%s" % os.getenv("SAXONC_EDITION", "he"))
