<?php

namespace Saft\Sparql\Test\Query;

use Saft\TestCase;
use Saft\Sparql\Query\SelectQuery;

class SelectQueryUnitTest extends TestCase
{
    public function setUp()
    {
        $this->fixture = new SelectQuery();
    }
    
    /**
     * Tests constructor
     */

    public function testConstructor()
    {
        $instanceToCheckAgainst = new SelectQuery();
        $instanceToCheckAgainst->init('SELECT ?x FROM <'. $this->testGraphUri .'> WHERE {?x ?y ?z}');
        
        $this->assertEquals(
            $instanceToCheckAgainst,
            new SelectQuery('SELECT ?x FROM <'. $this->testGraphUri .'> WHERE {?x ?y ?z}')
        );
    }

    /**
     * Tests getQueryParts
     */

    public function testGetQueryParts()
    {
        $this->fixture->init(
            'PREFIX foo: <http://bar.de/>
             SELECT ?s ?p ?o
               FROM <http://foo/bar/>
               FROM NAMED <http://foo/bar/named>
              WHERE {
                    ?s ?p ?o.
                    ?s?p?o.
                    ?s <http://www.w3.org/2000/01/rdf-schema#label> "Foo"^^<http://www.w3.org/2001/XMLSchema#string> .
                    ?s ?foo "val EN"@en
                    FILTER (?o = "Bar")
                    FILTER (?o > 40)
                    FILTER regex(?g, "r", "i")
               }
              LIMIT 10
             OFFSET 5 '
        );

        $queryParts = $this->fixture->getQueryParts();
        
        // Checks the return for the following patterns:
        // FILTER (?o = 'Bar')
        // FILTER (?o > 40)
        // FILTER regex(?g, 'r', 'i')
        $this->assertEquals(
            array(
                array(
                    'type'      => 'expression',
                    'sub_type'  => 'relational',
                    'patterns'  => array(
                        array(
                            'value'     => 'o',
                            'type'      => 'var',
                            'operator'  => ''
                        ),
                        array(
                            'value'     => 'Bar',
                            'type'      => 'literal',
                            'sub_type'  => 'literal2',
                            'operator'  => ''
                        )
                    ),
                    'operator'  => '='
                ),

                // FILTER (?o > 40)
                array(
                    'type'      => 'expression',
                    'sub_type'  => 'relational',
                    'patterns'  => array(
                        array(
                            'value'     => 'o',
                            'type'      => 'var',
                            'operator'  => ''
                        ),
                        array(
                            'value'     => '40',
                            'type'      => 'literal',
                            'operator'  => '',
                            'datatype'  => 'http://www.w3.org/2001/XMLSchema#integer'
                        )
                    ),
                    'operator'  => '>'
                ),

                // FILTER regex(?g, 'r', 'i')
                array(
                    'args' => array(
                        array(
                            'value' => 'g',
                            'type' => 'var',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'r',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                        array(
                            'value' => 'i',
                            'type' => 'literal',
                            'sub_type' => 'literal2',
                            'operator' => ''
                        ),
                    ),
                    'type' => 'built_in_call',
                    'call' => 'regex',
                ),
            ),
            $queryParts['filter_pattern']
        );

        // graphs
        $this->assertEquals(array('http://foo/bar/'), $queryParts['graphs']);

        // named graphs
        $this->assertEquals(array('http://foo/bar/named'), $queryParts['named_graphs']);

        // triple patterns
        // Checks the return for the following patterns:
        // ?s ?p ?o.
        // ?s?p?o.
        // ?s <http://www.w3.org/2000/01/rdf-schema#label> \'Foo\'^^<http://www.w3.org/2001/XMLSchema#string> .
        // ?s ?foo \'val EN\'@en .
        $this->assertEquals(
            array(
                // ?s ?p ?o.
                array(
                    's'             => 's',
                    'p'             => 'p',
                    'o'             => 'o',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'var',
                    'o_datatype'    => '',
                    'o_lang'        => ''
                ),
                // ?s?p?o.
                array(
                    's'             => 's',
                    'p'             => 'p',
                    'o'             => 'o',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'var',
                    'o_datatype'    => '',
                    'o_lang'        => ''
                ),
                // ?s <http://www.w3.org/2000/01/rdf-schema#label>
                //    \'Foo\'^^<http://www.w3.org/2001/XMLSchema#string> .
                array(
                    's'             => 's',
                    'p'             => 'http://www.w3.org/2000/01/rdf-schema#label',
                    'o'             => 'Foo',
                    's_type'        => 'var',
                    'p_type'        => 'uri',
                    'o_type'        => 'typed-literal',
                    'o_datatype'    => 'http://www.w3.org/2001/XMLSchema#string',
                    'o_lang'        => ''
                ),
                // ?s ?foo \'val EN\'@en .
                array(
                    's'             => 's',
                    'p'             => 'foo',
                    'o'             => 'val EN',
                    's_type'        => 'var',
                    'p_type'        => 'var',
                    'o_type'        => 'literal',
                    'o_datatype'    => '',
                    'o_lang'        => 'en'
                )
            ),
            $queryParts['triple_pattern']
        );

        /**
         * limit
         */
        $this->assertEquals('10', $queryParts['limit']);

        /**
         * offset
         */
        $this->assertEquals('5', $queryParts['offset']);
        
        /**
         * prefixes
         */
        $this->assertEquals(
            array(
                'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
                'xsd'  => 'http://www.w3.org/2001/XMLSchema#'
            ),
            $queryParts['namespaces']
        );
        
        /**
         * prefixes
         */
        $this->assertEquals(array('foo' => 'http://bar.de/'), $queryParts['prefixes']);
        
        /**
         * result vars
         */
        $this->assertEquals(array('s', 'p', 'o'), $queryParts['result_variables']);
        
        /**
         * variables
         */
        $this->assertEquals(array('s', 'p', 'o', 'foo', 'g'), $queryParts['variables']);
    }
    
    /**
     * Tests init
     */
    public function testInit()
    {
        $this->fixture = new SelectQuery();
        $this->fixture->init(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.}'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        // select
        $this->assertEquals('SELECT ?s ?p ?o', $queryParts['select']);
        
        // from
        $this->assertEquals(array($this->testGraphUri), $queryParts['graphs']);
        
        // where
        $this->assertEquals('WHERE {?s ?p ?o.}', $queryParts['where']);
    }
    
    public function testInitWithLimit()
    {
        $this->fixture = new SelectQuery();
        $this->fixture->init(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} LIMIT 10'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        // limit
        $this->assertEquals('10', $queryParts['limit']);
    }
    
    public function testInitWithOffset()
    {
        $this->fixture = new SelectQuery();
        $this->fixture->init(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} Offset 5'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        // offset
        $this->assertEquals('5', $queryParts['offset']);
    }
    
    public function testInitWithLimitOffset()
    {
        $this->fixture = new SelectQuery();
        $this->fixture->init(
            'SELECT ?s ?p ?o FROM <'. $this->testGraphUri .'> WHERE {?s ?p ?o.} LIMIT 10 OFFSET 5'
        );
        
        $queryParts = $this->fixture->getQueryParts();
        
        // select
        $this->assertEquals('SELECT ?s ?p ?o', $queryParts['select']);
        
        // from
        $this->assertEquals(array($this->testGraphUri), $queryParts['graphs']);
        
        // where
        $this->assertEquals('WHERE {?s ?p ?o.}', $queryParts['where']);
        
        // limit
        $this->assertEquals('10', $queryParts['limit']);
        
        // offset
        $this->assertEquals('5', $queryParts['offset']);
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
        $this->assertTrue($this->fixture->isSelectQuery());
    }
    
    /**
     * Tests isUpdateQuery
     */
     
    public function testIsUpdateQuery()
    {
        $this->assertFalse($this->fixture->isUpdateQuery());
    }
}