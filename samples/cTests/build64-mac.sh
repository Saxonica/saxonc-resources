#!/bin/sh

jdkdir=../../Saxon.C.API/graalvm

gcc -m64 -fPIC -I$jdkdir ../../Saxon.C.API/SaxonCGlue.c ../../Saxon.C.API/SaxonCProcessor.c ../../Saxon.C.API/SaxonCXPath.c  testXSLT.c -o testXSLT -ldl -lsaxon-@@edition@@c-@@VERSION@@  $1

gcc -m64 -fPIC -I$jdkdir ../../Saxon.C.API/SaxonCGlue.c ../../Saxon.C.API/SaxonCProcessor.c  testXQuery.c -o testXQuery -ldl -lsaxon-@@edition@@c-@@VERSION@@ $1
	
gcc -m64 -fPIC -I$jdkdir ../../Saxon.C.API/SaxonCGlue.c ../../Saxon.C.API/SaxonCProcessor.c ../../Saxon.C.API/SaxonCXPath.c testXPath.c -o testXPath -ldl -lsaxon-@@edition@@c-@@VERSION@@ $1

