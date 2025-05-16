cmake_minimum_required(VERSION 3.27)
project(saxonc_samples)

set(CMAKE_CXX_STANDARD 14)

set(THREADS_PREFER_PTHREAD_FLAG ON)
find_package(Threads REQUIRED)

set(SaxonCHE_DIR ../SaxonCHE/lib/cmake/SaxonCHE)
find_package(SaxonCHE 12.7.0 EXACT REQUIRED)

add_executable(CTestXPath cTests/testXPath.c)
target_link_libraries(CTestXPath PRIVATE SaxonCHE::saxonc-he)

add_executable(CTestXQuery cTests/testXQuery.c)
target_link_libraries(CTestXQuery PRIVATE SaxonCHE::saxonc-he)

add_executable(CTestXslt cTests/testXSLT.c)
target_link_libraries(CTestXslt PRIVATE SaxonCHE::saxonc-he)

add_executable(CppTestXPath cppTests/testXPath.cpp)
target_link_libraries(CppTestXPath PRIVATE SaxonCHE::saxonc-he)

add_executable(CppTestXQuery cppTests/testXQuery.cpp)
target_link_libraries(CppTestXQuery PRIVATE SaxonCHE::saxonc-he)

add_executable(CppTestXslt cppTests/testXSLT30.cpp)
target_link_libraries(CppTestXslt PRIVATE SaxonCHE::saxonc-he)
target_link_libraries(CppTestXslt PRIVATE Threads::Threads)
