import os
import importlib

def import_mod():
    """
    This replicates `import saxonche`
    :return: the saxonc module
    """
    return importlib.import_module("saxonc%s" % os.getenv("SAXONC_EDITION", "he"))

def import_star(globals):
    """
    This mostly replicates `from saxonche import *`
    :param globals: the result of calling globals() in the importing module
    :return: the saxonc module
    """
    saxonc = import_mod()
    saxoncModuleObjects = [(name, getattr(saxonc, name)) for name in dir(saxonc)]
    saxoncClasses = { cls[0]: cls[1] for cls in saxoncModuleObjects if isinstance(cls[1], type) }
    for name, cls in saxoncClasses.items():
        globals[name] = cls
    globals['create_xdm_dict'] = saxonc.create_xdm_dict
    return saxonc
