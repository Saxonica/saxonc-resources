from saxonche import *
proc = PySaxonProcessor()
json1 = """{ "test" : "This is a test. Price is higher than 25 €. " }"""

parsed_json1 = proc.parse_json(json_text=json1)
print(type(parsed_json1.head))
