#include "saxonc/SaxonCProcessor.h"
#include <stdio.h> /* defines FILENAME_MAX */
#if defined(__APPLE__) || defined __linux__
#include <unistd.h>
#define GetCurrentDir getcwd
#else
#include <direct.h>
#define GetCurrentDir _getcwd
#endif


void path_join(char* result, const char* segment1, const char* segment2) {
  int path_len = strlen(segment1) + strlen(segment2) + 1;
  snprintf(result, path_len, "%s%s", segment1, segment2);
}

int main(int argc, char *argv[]) {
  const char * const data_dir = argc > 1 ? argv[1] : "";
  char cwd[FILENAME_MAX]; // create string buffer to hold path
  GetCurrentDir(cwd, FILENAME_MAX);
  printf("CWD = %s\n", cwd);

  int cap = 10;
  sxnc_parameter *parameters; /*!< array of paramaters used for the
                                 transformation as (string, value) pairs */
  int parLen, parCap;
  parLen = 0;
  parCap = cap;
  sxnc_property *properties; /*!< array of properties used for the
                                transformation as (string, string) pairs */
  int propLen, propCap;
  propCap = cap;
  propLen = 0;
  sxnc_environment *environi;
  sxnc_processor *processor;

  initSaxonc(&environi, &processor, &parameters, &properties, parCap, propCap);

  /*
   * Initialize Graalvm run-time.
   * The handle of loaded component is used to retrieve Invocation API.
   */

  create_graalvm_isolate(environi);
  int checkProc = c_createSaxonProcessor(environi, processor, 0);

  if (!checkProc) {
    const char *errorMessage = c_getErrorMessage(environi);
    if (errorMessage) {
      printf("Error message: %s \n", errorMessage);
    }
    return -1;
  }

  // setDebugMode(environi->thread, 1);

  char cat_xml_file[FILENAME_MAX];
  path_join(cat_xml_file, data_dir, "/cat.xml");

  const char *verCh = getProductVariantAndVersion(environi, processor);
  printf("XQuery Tests\n\nSaxon version: %s \n", verCh);
  setProperty(&properties, &propLen, &propCap, "s", cat_xml_file);

  setProperty(&properties, &propLen, &propCap, "cwd", cwd);

  setProperty(&properties, &propLen, &propCap, "qs",
              "<out>{count(/out/person)}</out>");

  const char *result =
      executeQueryToString(environi, processor, cwd, 0, properties, 0, propLen);

  if (!result) {
    printf("result is null\n");
    const char *errorMessage = c_getErrorMessage(environi);
    if (errorMessage) {
      printf("Error message %s \n", errorMessage);
    }
  } else {
    printf("%s \n", result);
  }
  fflush(stdout);

  graal_tear_down(environi->thread);
  freeSaxonc(&environi, &processor, &parameters, &properties);

  return 0;
}
