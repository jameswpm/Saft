<?php

namespace Saft\Sparql\Test\Query;

use Saft\TestCase;
use Saft\Sparql\Query\AbstractQuery;
use Saft\Sparql\Query\UpdateQuery;

class UpdateQueryUnitTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new UpdateQuery();
    }
    
    /**
     * Tests constructor
     */

    public function testConstructor()
    {
        $this->fixture = new UpdateQuery(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }'
        );
        
        $this->assertEquals(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }',
            $this->fixture->getQuery()
        );
    }
        
    /**
     * Tests extractGraphs
     */

    public function testExtractGraphsDeleteData()
    {
        $this->fixture = AbstractQuery::initByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA { 
                Graph <http://saft/test/g1> {<http://saft/test/s1> dc:p1 <http://saft/test/o1>}
                Graph <http://saft/test/g2> {<http://saft/test/s1> dc:p1 <http://saft/test/o1>}
            }'
        );
        
        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(
            array('http://saft/test/g1', 'http://saft/test/g2'),
            $queryParts['graphs']
        );
    }

    public function testExtractGraphsInsertIntoGraph()
    {
        $this->fixture = AbstractQuery::initByQueryString(
            'PREFIX dc: <http://foo/bar/>
            INSERT DATA { Graph <http://saft/test/g1> {
                <http://saft/test/s1> dc:p1 <http://saft/test/o1>}
            }'
        );
        
        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(array('http://saft/test/g1'), $queryParts['graphs']);
    }
        
    /**
     * Tests extractNamespacesFromQuery
     */

    public function testExtractNamespacesFromQuery()
    {
        $this->fixture = AbstractQuery::initByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA { GRAPH <http://> { ?s dc: ?o. ?s <http://foo/sss> ?o } }'
        );
        
        $queryParts = $this->fixture->getQueryParts();

        $this->assertEquals(array('ns-0' => 'http://foo/'), $queryParts['namespaces']);
    }

    public function testExtractNamespacesFromQueryNoNamespaces()
    {
        $this->fixture = AbstractQuery::initByQueryString(
            'PREFIX dc: <http://foo/bar/>
            DELETE DATA { GRAPH <http://> { ?s ?p ?o } }'
        );
        
        $queryParts = $this->fixture->getQueryParts();

        $this->assertFalse(isset($queryParts['namespaces']));
    }

    /**
     * Tests extractPrefixesFromQuery
     */

    public function testExtractPrefixesFromQuery()
    {
        // assumption here is that fixture is of type
        $this->fixture = AbstractQuery::initByQueryString(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> DELETE { ?x foaf:name "Alice" } WHERE { ?s ?p ?o }'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        $this->assertEquals(array('foaf' => 'http://xmlns.com/foaf/0.1/'), $queryParts['prefixes']);
    }
    
    public function testExtractPrefixesFromQueryNoPrefixes()
    {
        // assumption here is that fixture is of type
        $this->fixture = AbstractQuery::initByQueryString(
            'DELETE DATA { GRAPH <http://> { ?s ?p ?o } }'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        $this->assertFalse(isset($queryParts['prefixes']));
    }
    
    /**
     * Tests getQueryParts
     */

    public function testGetSubTypeDeleteData()
    {
        $this->fixture = new UpdateQuery('
            PREFIX dc: <http://foo/bar/> DELETE DATA { GRAPH <http://> { ?s ?p ?o } }');
        
        $this->assertEquals('deleteData', $this->fixture->getSubType());
    }

    public function testGetSubTypeInsertData()
    {
        $this->fixture = new UpdateQuery(
            'PREFIX dc: <http://foo/bar/> INSERT DATA { GRAPH <http://> { ?s dc:foo "hi" } }'
        );
        
        $this->assertEquals('insertData', $this->fixture->getSubType());
    }

    public function testGetSubTypeInsertInto()
    {
        $this->fixture = new UpdateQuery(
            'PREFIX dc: <http://foo/bar/> INSERT INTO GRAPH <http://> { ?s dc:foo "hi" }'
        );
        
        $this->assertEquals('insertInto', $this->fixture->getSubType());
    }

    public function testGetSubTypeWithDeleteInsertWhere()
    {
        $this->fixture = new UpdateQuery(
            'PREFIX dc: <http://foo/bar/>
             WITH <http://> DELETE { ?s dc:foo "hi" } INSERT { ?s dc:foo "ho" } WHERE { ?s dc:foo "hi" }'
        );
        
        $this->assertEquals('withDeleteInsertWhere', $this->fixture->getSubType());
    }

    public function testGetSubTypeWithDeleteWhere()
    {
        $this->fixture = new UpdateQuery(
            'PREFIX dc: <http://foo/bar/> WITH <http://> DELETE { ?s dc:foo "hi" } WHERE { ?s dc:foo "hi" }'
        );
        
        $this->assertEquals('withDeleteWhere', $this->fixture->getSubType());
    }
    
    /**
     * Tests getQueryParts
     */

    public function testGetQueryPartsEverything()
    {
        $this->fixture->init(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            WITH <http://graph/> 
            DELETE { ?x foaf:name "Alice"^^<http://www.w3.org/2001/XMLSchema#string>. ?x <http://namespace/aa> ?y } 
            WHERE { ?s ?p ?o. FILTER(?o < 40) }'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        $this->assertEquals(9, count($queryParts));
        
        $this->assertEquals(
            '?x foaf:name "Alice"^^<http://www.w3.org/2001/XMLSchema#string>. ?x <http://namespace/aa> ?y',
            $queryParts['deleteData']
        );
        $this->assertEquals('?s ?p ?o. FILTER(?o < 40)', $queryParts['deleteWhere']);
        $this->assertEquals(
            array(
                array(
                    'type' => 'expression',
                    'sub_type' => 'relational',
                    'patterns' => array(
                        array(
                            'value' => 'o',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => '40',
                            'type' => 'literal',
                            'operator' => '',
                            'datatype' => 'http://www.w3.org/2001/XMLSchema#integer'
                        ),
                    ),
                    'operator' => '<'
                )
            ),
            $queryParts['filter_pattern']
        );
        $this->assertEquals(array('http://graph/'), $queryParts['graphs']);
        $this->assertEquals(
            array('ns-0' => 'http://namespace/', 'xsd' => 'http://www.w3.org/2001/XMLSchema#'),
            $queryParts['namespaces']
        );
        $this->assertEquals(array('foaf' => 'http://xmlns.com/foaf/0.1/'), $queryParts['prefixes']);
        $this->assertEquals('withDeleteWhere', $queryParts['sub_type']);
        $this->assertEquals(
            array(
                array(
                    's' => 'x',
                    'p' => 'http://xmlns.com/foaf/0.1/name',
                    'o' => 'Alice',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'typed-literal',
                    'o_datatype' => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang' => null
                ),
                array(
                    's' => 'x',
                    'p' => 'http://namespace/aa',
                    'o' => 'y',
                    's_type' => 'var',
                    'p_type' => 'uri',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                ),
                array(
                    's' => 's',
                    'p' => 'p',
                    'o' => 'o',
                    's_type' => 'var',
                    'p_type' => 'var',
                    'o_type' => 'var',
                    'o_datatype' => null,
                    'o_lang' => null
                ),
            ),
            $queryParts['triple_pattern']
        );
        $this->assertEqualsArrays(array('s', 'p', 'o', 'x', 'y'), $queryParts['variables']);
    }
    
    /**
     * Tests init
     */
     
    public function testInitDeleteData()
    {
        $this->fixture = new UpdateQuery();
        $this->fixture->init(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE DATA { ?x foaf:name "Alice" }'
        );
        
        $this->assertEquals(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            DELETE DATA { ?x foaf:name "Alice" }',
            $this->fixture->getQuery()
        );
    }
    
    /**
     * Tests isAskQuery
     */
     
    public function testIsAskQuery()
    {
        $this->assertFalse($this->fixture->isAskQuery());
    }
    
    /**
     * Tests isDescribeQuery
     */
     
    public function testIsDescribeQuery()
    {
        $this->assertFalse($this->fixture->isDescribeQuery());
    }
    
    /**
     * Tests isGraphQuery
     */
     
    public function testIsGraphQuery()
    {
        $this->assertFalse($this->fixture->isGraphQuery());
    }
    
    /**
     * Tests isSelectQuery
     */
     
    public function testIsSelectQuery()
    {
        $this->assertFalse($this->fixture->isSelectQuery());
    }
    
    /**
     * Tests isUpdateQuery
     */
     
    public function testIsUpdateQuery()
    {
        $this->assertTrue($this->fixture->isUpdateQuery());
    }
}