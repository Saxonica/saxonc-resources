<?php
/*declare(strict_types=1);*/
use PHPUnit\Framework\TestCase;

// Download phpunit file: wget -O phpunit.phar https://phar.phpunit.de/phpunit-10.phar
final class SaxonCPHPTests extends TestCase
{
    protected static $saxonProc;
    public static function setUpBeforeClass(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: setupBeforeClass");
        }
        self::$saxonProc = new Saxon\SaxonProcessor(False);

    }
    public function testCreateXslt30ProcessorObject(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCreateXslt30ProcessorObject");
        }
        $proc = self::$saxonProc->newXslt30Processor();
        $this->assertInstanceOf(
            Saxon\Xslt30Processor::class,
            $proc
        );
    }

    public function testVersion(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testVersion");
        }
        $this->assertStringContainsString(
            '12.4',
            self::$saxonProc->version()
        );
        $this->assertStringContainsString('from Saxonica', self::$saxonProc->version());
    }
    public function testSchemaAware(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testSchemaAware");
        }
        $this->assertFalse(
            self::$saxonProc->isSchemaAware()
        );
    }
    public function testSchemaAware2(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testSchemaAware2");
        }
        $saxonProc2 = new Saxon\SaxonProcessor(True);
        $this->assertFalse($saxonProc2->isSchemaAware());
    }
    public function testContextNotRoot(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testContextNotRoot");
        }
        $trans = self::$saxonProc->newXslt30Processor();
        $node = self::$saxonProc->parseXmlFromString("<doc><e>text</e></doc>");
        $executable = $trans->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'><xsl:variable name='x' select='.'/><xsl:template match='/'>errorA</xsl:template><xsl:template match='e'>[<xsl:value-of 
select='name(\$x)'/>]</xsl:template></xsl:stylesheet>");
        $this->assertNotNull($node);
        $this->assertInstanceOf(
            Saxon\XdmNode::class,
            $node
        );
        $this->assertTrue($node->getChildCount() > 0);
        $this->assertNotNull($node);
        $eNode = $node->getChildNode(0)->getChildNode(0);
        $this->assertNotNull($eNode);
        $executable->setGlobalContextItem($node);
        $executable->setInitialMatchSelection($eNode);
        $result = $executable->applyTemplatesReturningString();
        $this->assertStringContainsString("[", $result);
    }
    /*  public function testResolveUri(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testResolveUri");
        }

        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString("<xsl:stylesheet version='3.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform' xmlns:xs='http://www.w3.org/2001/XMLSchema' xmlns:err='http://www.w3.org/2005/xqt-errors'><xsl:template 
name='go'><xsl:try><xsl:variable name='uri' as='xs:anyURI' select=\"resolve-uri('notice trailing space /out.xml')\"/> <xsl:message select='\$uri'/><xsl:result-document href='{\$uri}'><out/></xsl:result-document><xsl:catch><xsl:sequence select=\"'\$err:code: ' || 
\$err:code  || ', \$err:description: ' || \$err:description\"/></xsl:catch></xsl:try></xsl:template></xsl:stylesheet>");
        $value = $executable->callTemplateReturningValue("go");
        if($value != NULL) {
            $item = $value->getHead();
            $this->assertStringContainsString("code", $item->getStringValue());
        } else {
            $this->assertFalse(False);
        }
    }  */

    public function testParsingXMLError(): void
    {
        if (getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testParsingXMLError");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        // Load the source document
        try {
            $input = self::$saxonProc->parseXmlFromFile("../../data/error.xml");
            $this->assertTrue(True);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(True, 'Caught exception: '. $e->getMessage());
        }

    }

    public function testParsingXMLFromStringError(): void
    {
        if (getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testParsingXMLError");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        // Load the source document
        try {
            $input = self::$saxonProc->parseXmlFromString("<out>");
            $this->assertTrue(True);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(True, 'Caught exception: '. $e->getMessage());
        }

    }

    public function testEmbeddedStylesheet(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testEmbeddedStylesheet");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        // Load the source document
        try {
            $input = self::$saxonProc->parseXmlFromFile("../data/books.xml");
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: '. $e->getMessage());
        }
        //Console.WriteLine("=============== source document ===============");
        //Console.WriteLine(input.OuterXml);
        //Console.WriteLine("=========== end of source document ============");
        // Navigate to the xml-stylesheet processing instruction having the pseudo-attribute type=text/xsl;
        // then extract the value of the href pseudo-attribute if present
        $path = "/processing-instruction(xml-stylesheet)[matches(.,'type\\s*=\\s*[''\"\"]text/xsl[''\" \"]')]/replace(., '.*?href\\s*=\\s*[''\" \"](.*?)[''\" \"].*', '$1')";
        $xPathProcessor = self::$saxonProc->newXPathProcessor();
        $xPathProcessor->setContextItem($input);
        $hrefval = $xPathProcessor->evaluateSingle($path);
        $this->assertNotNull($hrefval);

        $this->assertInstanceOf(Saxon\XdmItem::class, $hrefval);
        $this->assertTrue($hrefval->isAtomic());
        $href = $hrefval->getAtomicValue()->getStringValue();
        $this->assertNotEquals($href, "");
        // The stylesheet is embedded in the source document and identified by a URI of the form "#id"
        $executable = $transformer->compileFromFile("../data/".$href);
        $this->assertInstanceOf(Saxon\XdmNode::class, $input);
        // Run it
        $node = $executable->transformToValue($input);
        echo $executable->getErrorMessage();

        $this->assertNotNull($node);
            echo $executable->getErrorMessage();
    }
    public function testContextNotRootNamedTemplate(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testContextNotRootNamedTemplate");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $node = self::$saxonProc->parseXmlFromString("<doc><e>text</e></doc>");
        $executable = $transformer->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'><xsl:variable name='x' select='.'/><xsl:template match='/'>errorA</xsl:template><xsl:template name='main'>[<xsl:value-of 
select='name(\$x)'/>]</xsl:template></xsl:stylesheet>");
        $executable->setGlobalContextItem($node);
        $result = $executable->callTemplateReturningValue("main");
        echo $executable->getErrorMessage();

        $this->assertNotNull($result);
        if ($result->getHead() != NULL) {   //$result->getHead(): XdmItem (ref++), result:XdmValue (ref++)   refCount = 2, ref--, refCount = 1
            $this->assertStringContainsString("[]", $result->getHead()->getStringValue());   //refCount=2
        }
        $result2 = $executable->callTemplateReturningString("main");
        echo $executable->getErrorMessage();

        $this->assertNotNull($result2);
        $this->assertStringContainsString("[]", $result2);
    }


    public function testContextNotRootNamedTemplate2(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testContextNotRootNamedTemplate");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'><xsl:variable name='x' select='.'/><xsl:template match='/'>errorA</xsl:template><xsl:template name='main'>[<xsl:value-of 
select='name(\$x)'/>]</xsl:template></xsl:stylesheet>");
        $executable->setGlobalContextItem(self::$saxonProc->parseXmlFromString("<doc><e>text</e></doc>"));
        $result = $executable->callTemplateReturningValue("main");
        echo $executable->getErrorMessage();

        $this->assertNotNull($result);
        if ($result->getHead() != NULL) {   //$result->getHead(): XdmItem (ref++), result:XdmValue (ref++)   refCount = 2, ref--, refCount = 1
            $this->assertStringContainsString("[]", $result->getHead()->getStringValue());   //refCount=2
        }
        $result2 = $executable->callTemplateReturningString("main");
        echo $executable->getErrorMessage();

        $this->assertNotNull($result2);
        $this->assertStringContainsString("[]", $result2);
    }

    public function testUseAssociated(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testAssociated");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $foo_xml = "trax/xml/foo.xml";
        $executable = $transformer->compileFromAssociatedFile($foo_xml);
        $executable->setInitialMatchSelectionAsFile($foo_xml);
        $result = $executable->applyTemplatesReturningString();
        $this->assertNotNull($result);
    }
    public function testTransformWithoutArgument1(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testTransformWithoutArgument1");
        }
        try {
            $transformer = self::$saxonProc->newXslt30Processor();
            $foo_xml = "trax/xml/foo.xml";
            $executable = $transformer->compileFromAssociatedFile($foo_xml);
            $executable->setInitialMatchSelectionAsFile($foo_xml);
            $result = $executable->transformToString();
            $this->assertNotNull($result);
        } catch(Exception $e) {
            //echo 'Caught exception: ',  $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: '.  $e->getMessage());

        }
    }

    public function testLineNumber(): void
    {
        if (getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testLineNumber");
        }
        $saxonProc1 = new Saxon\SaxonProcessor(False);
        $saxonProc1->setConfigurationProperty('http://saxon.sf.net/feature/linenumbering', 'on');
        $xpathProc = $saxonProc1->newXPathProcessor();
        // Load the source document
        try {
            $input = $saxonProc1->parseXmlFromFile("../data/books.xml");
            $this->assertNotNull($input);
            $xpathProc->setContextItem($input);
            $result = $xpathProc->evaluateSingle("//BOOKS/ITEM[@CAT='MMP']/TITLE");
            $this->assertNotNull($result);
            $linenum = $result->getNodeValue()->getLineNumber();
            $this->assertTrue($linenum == 7);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: ' . $e->getMessage());
        }
    }

    public function testColumnNumber(): void
    {
        if (getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testColumnNumber");
        }
        $saxonProc1 = new Saxon\SaxonProcessor(False);
        $saxonProc1->setConfigurationProperty('http://saxon.sf.net/feature/linenumbering', 'on');
        $xpathProc = $saxonProc1->newXPathProcessor();
        // Load the source document
        try {
            $input = $saxonProc1->parseXmlFromFile("../data/books.xml");
            $this->assertNotNull($input);
            $xpathProc->setContextItem($input);
            $result = $xpathProc->evaluateSingle("//BOOKS/ITEM[@CAT='MMP']/TITLE");
            $this->assertNotNull($result);
            $colnum = $result->getNodeValue()->getColumnNumber();
            $this->assertTrue($colnum == 14);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: ' . $e->getMessage());
        }
    }


    public function testTransformWithoutArgument2(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testTransformWithoutArgument2");
        }
        try {
            $transformer = self::$saxonProc->newXslt30Processor();
            $foo_xml = "trax/xml/foo.xml";
            $executable = $transformer->compileFromAssociatedFile($foo_xml);
            $executable->setInitialMatchSelectionAsFile($foo_xml);
            $resultValue = $executable->transformToValue();
            $this->assertNotNull($resultValue);
            $resultItem = $resultValue->getHead();
            $this->assertNotNull($resultItem);
            $result = $resultItem->getStringValue();
            $this->assertNotNull($result);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: '. $e->getMessage());

        }
    }
    public function testTransformWithoutArgument3(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testTransformWithoutArgument3");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $foo_xml = "trax/xml/foo.xml";
        $executable = $transformer->compileFromAssociatedFile($foo_xml);
        $executable->setInitialMatchSelectionAsFile($foo_xml);
        $executable->setOutputFile("resultForTransformWithoutArgument.xml");
        $executable->transformToFile();
        $this->assertTrue(file_exists("resultForTransformWithoutArgument.xml"), "resultForTransformWithoutArgument.xml not found");
                if (file_exists("resultForTransformWithoutArgument.xml")) {
                    unlink("resultForTransformWithoutArgument.xml");
                }
    }

    public function testXdmDestination(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testXdmDestination");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" .
            "<xsl:template name='go'><a/></xsl:template>" .
            "</xsl:stylesheet>");
        $root = $executable->callTemplateReturningValue("go");
        $this->assertNotNull($root);
        $this->assertNotNull($root->getHead());
        $node = $root->getHead()->getNodeValue();
        $this->assertTrue($node->getNodeKind() == 9, "result is document node");
    }
    public function testXdmDestinationWithItemSeparator(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testXdmDestinationWithItemSeparator");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" .
            "<xsl:template name='go'><xsl:comment>A</xsl:comment><out/><xsl:comment>Z</xsl:comment></xsl:template>" .
            "<xsl:output method='xml' item-separator='§'/>" .
            "</xsl:stylesheet>");
        $root = $executable->callTemplateReturningValue("go");
        $node = $root->getHead()->getNodeValue();
        $this->assertEquals("<!--A-->§<out/>§<!--Z-->", $node);
        $this->assertTrue($node->getNodeKind() == 9, "result is document node");
    }
    public function testPipeline(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testPipeline");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $xsl = "<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" .
            "<xsl:template match='/'><a><xsl:copy-of select='.'/></a></xsl:template>" .
            "</xsl:stylesheet>";
        $xml = "<z/>";
        $xsltProc = self::$saxonProc->newXslt30Processor();

        $in = self::$saxonProc->parseXmlFromString($xml);

        $stage1 = $xsltProc->compileFromString($xsl);
        $stage2 = $xsltProc->compileFromString($xsl);

        $stage3 = $xsltProc->compileFromString($xsl);

        $stage4 = $xsltProc->compileFromString($xsl);

        $stage5 = $xsltProc->compileFromString($xsl);
        $this->assertNotNull($xsl, "\$xsl check");
        $this->assertNotNull($in, "\$in check");
        $stage1->setProperty("!omit-xml-declaration", "yes");
        $stage1->setProperty("!indent", "no");
        $stage1->setInitialMatchSelection($in);
        $d1 = $stage1->applyTemplatesReturningValue();
        if ($stage1->exceptionOccurred()) {
            echo $stage1->getErrorMessage(0);
        }
        $this->assertNotNull($d1, "\$d1 check");
        $stage2->setProperty("!omit-xml-declaration", "yes");
        $stage2->setProperty("!indent", "no");
        $stage2->setInitialMatchSelection($d1);
        $d2 = $stage2->applyTemplatesReturningValue();
        $this->assertNotNull($d2, "\$d2");
        $stage3->setProperty("!omit-xml-declaration", "yes");
        $stage3->setProperty("!indent", "no");
        $stage3->setInitialMatchSelection($d2);
        $d3 = $stage3->applyTemplatesReturningValue();
        $this->assertNotNull($d3, "\$d3 check");
        $stage4->setProperty("!omit-xml-declaration", "yes");
        $stage4->setProperty("!indent", "no");
        $stage4->setInitialMatchSelection($d3);
        $d4 = $stage4->applyTemplatesReturningValue();
        $this->assertNotNull($d3, "\$d4 check");
        $stage5->setProperty("!indent", "no");
        $stage5->setProperty("!omit-xml-declaration", "yes");
        $stage5->setInitialMatchSelection($d4);
        $sw = $stage5->applyTemplatesReturningString();
        $this->assertNotNull($sw, "\$sw check");
        $this->assertStringContainsString($sw, "<a><a><a><a><a><z/></a></a></a></a></a>");
    }
    public function testPipelineShort(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testPipelineShort");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $xsl = "<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" .
            "<xsl:template match='/'><a><xsl:copy-of select='.'/></a></xsl:template>" .
            "</xsl:stylesheet>";
        $xml = "<z/>";

        $xsltProc = self::$saxonProc->newXslt30Processor();

        $stage1 = $xsltProc->compileFromString($xsl);
        $stage2 = $xsltProc->compileFromString($xsl);
        $this->assertNotNull($xsl);
        $stage1->setProperty("!omit-xml-declaration", "yes");
        $stage2->setProperty("!omit-xml-declaration", "yes");
        $in = self::$saxonProc->parseXmlFromString($xml);
        $this->assertNotNull($in, "\$sin check");
        $stage1->setInitialMatchSelection($in);
        $out = $stage1->applyTemplatesReturningValue();
        $this->assertNotNull($out, "\$out check");
        $stage2->setInitialMatchSelection($out);
        $sw = $stage2->applyTemplatesReturningString();
        $this->assertStringContainsString($sw, "<a><a><z/></a></a>");
    }
    /*
        public function testSchemaAware11(): void {
            // Create a Processor instance.
            try {

                Processor proc = new Processor(true);
                proc.setConfigurationProperty(FeatureKeys.XSD_VERSION, "1.1");
                Xslt30Processor transformer = new Xslt30Processor(proc);
                transformer.compileFromString(null,
                        "<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" +
                                "<xsl:import-schema><xs:schema xmlns:xs='http://www.w3.org/2001/XMLSchema'>" +
                                "<xs:element name='e'><xs:complexType><xs:sequence><xs:element name='p'/></xs:sequence><xs:assert test='exists(p)'/></xs:complexType></xs:element>" +
                                "</xs:schema></xsl:import-schema>" +
                                "<xsl:variable name='v'><e><p/></e></xsl:variable>" +
                                "<xsl:template name='main'><xsl:copy-of select='$v' validation='strict'/></xsl:template>" +
                                "</xsl:stylesheet>", null, null);


                String[] params = new String[]{"!indent"};
                Object[] values = new Object[]{"no"};

                String sw = transformer.callTemplateReturningString(null, null,  "main", params, values);
                assertTrue(sw.contains("<e>"));
            } catch (SaxonApiException e) {
                e.printStackTrace();
                fail(e.getMessage());
            }

        }
    */
    public function testCallFunction()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallFunction");
        }
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                xmlns:f='http://localhost/'" .
            "                version='3.0'>" .
            "                <xsl:function name='f:add' visibility='public'>" .
            "                  <xsl:param name='a'/><xsl:param name='b'/>" .
            "                  <xsl:sequence select='\$a + \$b'/></xsl:function>" .
            "                </xsl:stylesheet>";
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString($source);
        $paramArr = array(self::$saxonProc->createAtomicValue(2), self::$saxonProc->createAtomicValue(3));
        $v = $executable->callFunctionReturningValue("{http://localhost/}add", $paramArr);
        $this->assertInstanceOf(Saxon\XdmItem::class, $v->getHead());
        $this->assertTrue($v->getHead()->isAtomic());
        $this->assertEquals($v->getHead()->getAtomicValue()->getLongValue(), 5);
    }
    public function testCallFunctionArgConversion()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallFunctionArgConversion");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                xmlns:f='http://localhost/'" .
            "                version='3.0'>" .
            "                <xsl:function name='f:add' visibility='public'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:double'/>" .
            "                   <xsl:sequence select='\$a + \$b'/>" .
            "                </xsl:function>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $v = $executable->callFunctionReturningValue("{http://localhost/}add", array(self::$saxonProc->createAtomicValue(2), self::$saxonProc->createAtomicValue(3)));
        $this->assertInstanceOf(Saxon\XdmItem::class, $v->getHead());
        $this->assertTrue($v->getHead()->isAtomic());
        $this->assertEquals($v->getHead()->getAtomicValue()->getDoubleValue(), 5.0e0);
        $this->assertStringContainsString("double", $v->getHead()->getAtomicValue()->getPrimitiveTypeName());
    }
    public function testCallFunctionWrapResults()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallFunctionWrapResults");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                xmlns:f='http://localhost/'" .
            "                version='3.0'>" .
            "                <xsl:param name='x' as='xs:integer'/>" .
            "                <xsl:param name='y' select='.+2'/>" .
            "                <xsl:function name='f:add' visibility='public'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:double'/>" .
            "                   <xsl:sequence select='\$a + \$b + \$x + \$y'/>" .
            "                </xsl:function>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $executable->setProperty("!omit-xml-declaration", "yes");
        $executable->setParameter("x", self::$saxonProc->createAtomicValue(30));
        $executable->setGlobalContextItem(self::$saxonProc->createAtomicValue(20));
        $sw = $executable->callFunctionReturningString("{http://localhost/}add", array(self::$saxonProc->createAtomicValue(2), self::$saxonProc->createAtomicValue(3)));
        $this->assertEquals("57", $sw);
    }
    public function testCallFunctionArgInvalid()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallFunctionArgInvalid");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                xmlns:f='http://localhost/'" .
            "                version='2.0'>" .
            "                <xsl:function name='f:add'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:double'/>" .
            "                   <xsl:sequence select='\$a + \$b'/>" .
            "                </xsl:function>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        try {
            $v = $executable->callFunctionReturningValue("{http://localhost/}add", array(self::$saxonProc->createAtomicValue(2), self::$saxonProc->createAtomicValue(3)));
            $this->assertFalse(True);
        } catch (Exception $e) {
            $this->assertStringContainsString("Cannot invoke function add#2 externally", $e->getMessage());
        }
    }
    public function testCallNamedTemplateWithTunnelParams()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallNamedTemplateWithtunnelParams");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:template name='t'>" .
            "                   <xsl:call-template name='u'/>" .
            "                </xsl:template>" .
            "                <xsl:template name='u'>" .
            "                   <xsl:param name='a' as='xs:double' tunnel='yes'/>" .
            "                   <xsl:param name='b' as='xs:float' tunnel='yes'/>" .
            "                   <xsl:sequence select='\$a + \$b'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $executable->setProperty("!omit-xml-declaration", "yes");
        $executable->setProperty("tunnel", "true");
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $sw = $executable->callTemplateReturningString("t");
        $this->assertNotNull($sw);
        $this->assertEquals("17", $sw);
    }
    public function testCallTemplateRuleWithParams()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallTemplateRuleWithParams");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:template match='*'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:float'/>" .
            "                   <xsl:sequence select='name(.), \$a + \$b'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $executable->setProperty("!omit-xml-declaration", "yes");
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $in = self::$saxonProc->parseXmlFromString("<e/>");
        $executable->setInitialMatchSelection($in);
        $sw = $executable->applyTemplatesReturningString();
        $this->assertEquals("e 17", $sw);
    }
    public function testApplyTemplatesToXdm()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testApplyTemplateToXdm");
        }
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:template match='*'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:float'/>" .
            "                   <xsl:sequence select='., \$a + \$b'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString($source);
        $executable->setProperty("!omit-xml-declaration", "yes");
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $executable->setResultAsRawValue(True);
        $input = self::$saxonProc->parseXmlFromString("<e/>");
        $executable->setInitialMatchSelection($input);
        $result = $executable->applyTemplatesReturningValue();
        $this->assertNotNull($result);
        $this->assertEquals(2, $result->size());
        $first = $result->itemAt(0);
        $this->assertTrue($first->isNode());
        $this->assertEquals("e", $first->getNodeValue()->getNodeName());
        $second = $result->itemAt(1);
        $this->assertTrue($second->isAtomic());
        $this->assertEquals(17e0, $second->getAtomicValue()->getDoubleValue());
    }
    public function testResultDocument(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testResultDocument");
        }
        // bug 2771
        $xsl = "<xsl:stylesheet version=\"3.0\" \n" .
            "  xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\">\n" .
            "\n" .
            "  <xsl:template match='a'>\n" .
            "    <c>d</c>\n" .
            "  </xsl:template>\n" .
            "\n" .
            "  <xsl:template match='whatever'>\n" .
            "    <xsl:result-document href='out.xml'>\n" .
            "      <e>f</e>\n" .
            "    </xsl:result-document>\n" .
            "  </xsl:template>\n" .
            "\n" .
            "</xsl:stylesheet>";
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString($xsl);
        $input = self::$saxonProc->parseXmlFromString("<a>b</a>");
        $executable->setInitialMatchSelection($input);
        $xdmValue = $executable->applyTemplatesReturningValue();
        $this->assertEquals(1, $xdmValue->size());
    }
    public function testApplyTemplatesToFile()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testApplyTemplatesToFile");
        }
        $xsl = "<xsl:stylesheet version=\"3.0\" \n" .
            "  xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\">\n" .
            "\n" .
            "  <xsl:template match='a'>\n" .
            "    <c>d</c>\n" .
            "  </xsl:template>\n" .
            "</xsl:stylesheet>";
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString($xsl);
        $input = self::$saxonProc->parseXmlFromString("<a>b</a>");
        $executable->setOutputFile("output123.xml");
        $executable->setInitialMatchSelection($input);
        $executable->applyTemplatesReturningFile("output123.xml");
        $this->assertTrue(file_exists("output123.xml"));
        if (file_exists("output123.xml")) {
            unlink("output123.xml");
        }
    }
    public function testCallTemplateWithResultValidation(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallTemplatewithResultValidation");
        }
        try {
            $saxonProc2 = new Saxon\SaxonProcessor(True);
            $saxonProc2->setcwd("/var/www/html"); //Change according to dir setup
            $transformer = $saxonProc2->newXslt30Processor();
            $source = "<?xml version='1.0'?>" .
                "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
                "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
                "                version='3.0' exclude-result-prefixes='#all'>" .
                "                <xsl:import-schema><xs:schema><xs:element name='x' type='xs:int'/></xs:schema></xsl:import-schema>" .
                "                <xsl:template name='main'>" .
                "                   <xsl:result-document validation='strict'>" .
                "                     <x>3</x>" .
                "                   </xsl:result-document>" .
                "                </xsl:template>" .
                "                </xsl:stylesheet>";
            $executable = $transformer->compileFromString($source);
            $executable->setProperty("!omit-xml-declaration", "yes");
            $sw = $executable->callTemplateReturningString("main");
            $this->assertNotNull($sw);
            $this->assertEquals($sw, "<x>3</x>");
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->assertTrue(False, 'Caught exception: '. $e->getMessage());
        }
    }
    /*    public function testCallTemplateWithResultValidationFailure(): void {
            try {
                Xslt30Processor transformer = new Xslt30Processor(true);

                String source = "<?xml version='1.0'?>" +
                        "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" +
                        "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" +
                        "                version='3.0' expand-text='yes' exclude-result-prefixes='#all'>" +
                        "                <xsl:import-schema><xs:schema><xs:element name='x' type='xs:int'/></xs:schema></xsl:import-schema>" +
                        "                <xsl:param name='p'>zzz</xsl:param>" +
                        "                <xsl:template name='main'>" +
                        "                   <xsl:result-document validation='strict'>" +
                        "                     <x>{$p}</x>" +
                        "                   </xsl:result-document>" +
                        "                </xsl:template>" +
                        "                </xsl:stylesheet>";

                transformer.compileFromString(null, source, null, null);

                String[] params = new String[]{"!omit-xml-declaration"};
                Object[] pvalues = new Object[]{"yes"};
                String sw = transformer.callTemplateReturningString(null, null, "main", params, pvalues);
                fail("unexpected success");

            } catch (SaxonApiException e) {
                System.err.println("Failed as expected: " + e.getMessage());
            }
        }

    */
    public function testCallTemplateNoParamsRaw(): void
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallTemplateNoParamsRaw");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $executable = $transformer->compileFromString("<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" .
            "<xsl:template name='xsl:initial-template'><xsl:sequence select='42'/></xsl:template>" .
            "</xsl:stylesheet>");
        $executable->setResultAsRawValue(True);
        $result = $executable->callTemplateReturningValue();
        $this->assertNotNull($result);
        $this->assertNotNull($result->getHead());
        $this->assertTrue($result->getHead()->isAtomic());
        $this->assertEquals($result->getHead()->getAtomicValue()->getLongValue(), 42);
    }
    public function testCallNamedTemplateWithParamsRaw()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testCallNamedTemplateWithParamsRaw");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:template name='t'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:float'/>" .
            "                   <xsl:sequence select='\$a+1, \$b+1'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $executable->setResultAsRawValue(True);
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $val = $executable->callTemplateReturningValue("t");
        $this->assertNotNull($val);
        $this->assertEquals(2, $val->size());
        $this->assertTrue($val->itemAt(0)->isAtomic());
        $this->assertEquals(13, $val->itemAt(0)->getAtomicValue()->getLongValue());
        $this->assertTrue($val->itemAt(0)->isAtomic());
        $this->assertEquals(6, $val->itemAt(1)->getAtomicValue()->getLongValue());
    }
    /*
        public function testCatalog(){

            $transformer = self::$saxonProc->newXslt30Processor();
            Processor proc = transformer.getProcessor();


            try {
                XmlCatalogResolver.setCatalog(CWD_DIR+"../../catalog-test/catalog.xml", proc.getUnderlyingConfiguration(), true);

                transformer.applyTemplatesReturningValue(CWD_DIR+"../../catalog-test/", "example.xml","test1.xsl",null, null);
            } catch (XPathException e) {
                e.printStackTrace();
                fail();
            } catch (SaxonApiException e) {
                e.printStackTrace();
                fail();
            }

        }

    */
    public function testApplyTemplatesRaw()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testApplyTemplatesRaw");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:template match='*'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:float'/>" .
            "                   <xsl:sequence select='., \$a + \$b'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $node = self::$saxonProc->parseXmlFromString("<e/>");
        $executable->setResultAsRawValue(True);
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $executable->setInitialMatchSelection($node);
        $result = $executable->applyTemplatesReturningValue();
        $this->assertEquals(2, $result->size());
        $first = $result->itemAt(0);
        $this->assertNotNull($first);
        $this->assertTrue($first->isNode());
        $this->assertEquals($first->getNodeValue()->getNodeName(), "e");
        $second = $result->itemAt(1);
        $this->assertNotNull($second);
        $this->assertTrue($second->isAtomic());
        $this->assertEquals($second->getAtomicValue()->getDoubleValue(), "17e0");
    }
    public function testApplyTemplatesToSerializer()
    {
        if(getenv("SAXONC_DEBUG_FLAG")) {
            print("Test: testApplyTemplatesToSerializer");
        }
        $transformer = self::$saxonProc->newXslt30Processor();
        $source = "<?xml version='1.0'?>" .
            "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" .
            "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" .
            "                version='3.0'>" .
            "                <xsl:output method='text' item-separator='~~'/>" .
            "                <xsl:template match='.'>" .
            "                   <xsl:param name='a' as='xs:double'/>" .
            "                   <xsl:param name='b' as='xs:float'/>" .
            "                   <xsl:sequence select='., \$a + \$b'/>" .
            "                </xsl:template>" .
            "                </xsl:stylesheet>";
        $executable = $transformer->compileFromString($source);
        $executable->setProperty("!omit-xml-declaration", "yes");
        $executable->setResultAsRawValue(True);
        $executable->setInitialTemplateParameters(array("a" => self::$saxonProc->createAtomicValue(12), "b" => self::$saxonProc->createAtomicValue(5)));
        $executable->setInitialMatchSelection(self::$saxonProc->createAtomicValue(16));
        $sw = $executable->applyTemplatesReturningString();
        $this->assertEquals("16~~17", $sw);
    }



public function testSingle()
{
    $xml = "<out>".
        "   <person>text1</person>".
        "   <person>text2</person>".
        "   <person>text3</person>".
    "</out>";
    $xp = self::$saxonProc->newXPathProcessor();

    $xp->setContextItem(self::$saxonProc->parseXmlFromString($xml));
    $item = $xp->evaluateSingle("//person[1]");
    $this->assertInstanceOf(
        Saxon\XdmItem::class,
        $item
    );
    $this->assertEquals($item, "<person>text1</person>");
    }

public function testArray()
{
    $xp = self::$saxonProc->newXPathProcessor();

    $item = $xp->evaluateSingle("[1,2,3]");
    $this->assertInstanceOf(
        Saxon\XdmItem::class,
        $item
    );
    $this->assertTrue($item->isArray());
    $arrVal = $item->getArray();
    $this->assertEquals($arrVal->getStringValue(), "[1,2,3]");
    }
    /*  public function testItemSeparatorToSerializer(): void {
          try {

              String sr =
                      "<xsl:stylesheet version='2.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>" +
                              "  <xsl:template name='go'>"
                              + "<xsl:comment>start</xsl:comment><a><b/><c/></a><xsl:comment>end</xsl:comment>"
                              + "</xsl:template>" +
                              "</xsl:stylesheet>";
              $transformer = self::$saxonProc->newXslt30Processor();
              transformer.compileFromString(null, sr, null, null);
              String[] params = new String[]{"!method", "!indent", "!item-separator"};
              Object[] pvalues = new Object[]{"xml", "no", "+++"};
              String sw = transformer.callTemplateReturningString(null, null, "go", params, pvalues);
              System.err.println(sw);
              assertTrue(sw.contains("<!--start-->+++"));
              assertTrue(sw.contains("+++<!--end-->"));
              assertTrue(sw.contains("<a><b/><c/></a>"));
          } catch (Exception e) {
              e.printStackTrace();
              fail(e.getMessage());
          }

      }

      public function testSequenceResult() throws SaxonApiException {
          try {

              String source = "<?xml version='1.0'?>" +
                      "                <xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform'" +
                      "                xmlns:xs='http://www.w3.org/2001/XMLSchema'" +
                      "                version='3.0'>" +
                      "                <xsl:template name='xsl:initial-template' as='xs:integer+'>" +
                      "                     <xsl:sequence select='(1 to 5)' />        " +
                      "                </xsl:template>" +
                      "                </xsl:stylesheet>";

              $transformer = self::$saxonProc->newXslt30Processor();
              transformer.compileFromString(null, source, null, null);
              String[] params = new String[]{"outvalue"};
              Object[] pvalues = new Object[]{true};
              XdmValue res = transformer.callTemplateReturningValue(null, null, null, params, pvalues);
              int count = res.size();
              XdmAtomicValue value = (XdmAtomicValue) res.itemAt(0);
          } catch (SaxonApiException e) {
              e.printStackTrace();
              fail();
          }
      }

  */
}
