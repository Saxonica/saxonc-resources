#include <stdio.h> /* defines FILENAME_MAX */
#if defined(__APPLE__) || defined __linux__
#include <unistd.h>
#define GetCurrentDir getcwd
#else
#include <direct.h>
#define GetCurrentDir _getcwd
#endif

#include "saxonc/SaxonCProcessor.h"

int cap = 10;


int exists(const char *fname) {
  FILE *file;
  if ((file = fopen(fname, "r"))) {
    fclose(file);
    return 1;
  }
  return 0;
}

void path_join(char* result, const char* segment1, const char* segment2) {
  int path_len = strlen(segment1) + strlen(segment2) + 1;
  snprintf(result, path_len, "%s%s", segment1, segment2);
}

void test_xinclude(sxnc_environment *environi, sxnc_processor *processor, char *cwd, const char * const data_dir){
  sxnc_parameter *parameters;
  int parLen = 0, parCap;
  parCap = cap;
  sxnc_property *properties;
  int propLen = 0;

  parameters = (sxnc_parameter *)calloc(cap, sizeof(sxnc_parameter));
  properties = (sxnc_property *)calloc(cap, sizeof(sxnc_property));

  char ** catalogFiles = calloc(1, sizeof(char *));

  catalogFiles[0] = "catalog.xml";

  setCatalogFiles(environi, processor, cwd, catalogFiles, 1);
  setConfigurationProperty(environi, processor, "http://saxon.sf.net/feature/xinclude-aware", "on");

  char sample1_xml_file[FILENAME_MAX];
  path_join(sample1_xml_file, data_dir, "/sample1.xml");

  char identity_xsl_file[FILENAME_MAX];
  path_join(identity_xsl_file, data_dir, "/identity.xsl");
    const char* result =
        xsltApplyStylesheet(environi, processor, cwd, sample1_xml_file,
            identity_xsl_file, 0, properties, 0, 1);
    xsltSaveResultToFile(environi, processor, cwd, sample1_xml_file,
        identity_xsl_file, "xinclude-xslt-result.xml", 0, properties, 0, 1);
    if (!result) {
        printf("result is null \n");
        const char* errorMessage = c_getErrorMessage(environi);
        if (errorMessage) {
            printf("Error message %s \n", errorMessage);
        }
    }
    else {
        printf("Test 1 Pass: %s", result);
    }

    if (exists("xinclude-xslt-result.xml")) {
        printf("Test 2 - Pass: xinclude-xslt-result.xml exists \n");
    }
    else {
        printf("Test 2 - Fail: xinclude-xslt-result.xml not found \n");
    }
    free(catalogFiles);


}

int main(int argc, char *argv[]) {
  const char * const data_dir = argc > 1 ? argv[1] : "";

  char cwd[FILENAME_MAX]; // create string buffer to hold path
  GetCurrentDir(cwd, FILENAME_MAX);
  printf("CWD = %s\n", cwd);

  sxnc_parameter *parameters;
  int parLen = 0, parCap;
  parCap = cap;
  sxnc_property *properties;
  int propLen = 0;
  parCap = cap;
  sxnc_environment *environi;
  sxnc_processor *processor;

  initSaxonc(&environi, &processor, &parameters, &properties, parCap, parCap);

  create_graalvm_isolate(environi);

  int checkProc = c_createSaxonProcessor(environi, processor, 0);

  if (!checkProc) {
    const char *errorMessage = c_getErrorMessage(environi);
    if (errorMessage) {
      printf("Error message: %s \n", errorMessage);
    }
    return -1;
  }

  const char *verCh = getProductVariantAndVersion(environi, processor);
  printf("XSLT Tests\n\nSaxon version: %s \n", verCh);

  char cat_xml_file[FILENAME_MAX];
  path_join(cat_xml_file, data_dir, "/cat.xml");
  char test_xsl_file[FILENAME_MAX];
  path_join(test_xsl_file, data_dir, "/test.xsl");

  const char *result =
      xsltApplyStylesheet(environi, processor, cwd, cat_xml_file,
                          test_xsl_file, 0, 0, 0, 0);

  xsltSaveResultToFile(environi, processor, cwd, cat_xml_file,
                       test_xsl_file, "outputFile.xml", 0, 0, 0, 0);

  if (!result) {
    printf("result is null \n");
    const char *errorMessage = c_getErrorMessage(environi);
    if (errorMessage) {
      printf("Error message %s \n", errorMessage);
    }
  } else {
    printf("Test 1 Pass: %s", result);
  }

  if (exists("outputFile.xml")) {
    printf("Test 2 - Pass: outputFile.xml exists \n");
  } else {
    printf("Test 2 - Fail: outputFile.xml not found \n");
  }


  test_xinclude(environi, processor, cwd, data_dir);



  fflush(stdout);

  graal_tear_down(environi->thread);
  return 0;
}
