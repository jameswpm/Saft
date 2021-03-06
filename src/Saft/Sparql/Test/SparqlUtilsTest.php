<?php

namespace Saft\Sparql\Test;

use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\SparqlUtils;
use Saft\Test\TestCase;

class SparqlUtilsTest extends TestCase
{
    /*
     * Tests for statementIteratorToSparqlFormat
     */

    public function testStatementIteratorToSparqlFormatGraphGivenWithTripleAndQuad()
    {
        $sparqlUtils = new SparqlUtils(new StatementIteratorFactoryImpl());

        $triple = new StatementImpl(
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s1'),
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/p1'),
            new LiteralImpl(new NodeUtils(), "42")
        );

        $quad = new StatementImpl(
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s2'),
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/p2'),
            new LiteralImpl(new NodeUtils(), "43"),
            new NamedNodeImpl(new NodeUtils(), 'http://some/other/graph/2')
        );

        $this->assertEquals(
            'Graph <http://localhost/Saft/TestGraph/> {<http://saft/test/s1> <http://saft/test/p1> '.
            '"42"^^<http://www.w3.org/2001/XMLSchema#string> . } '.
            'Graph <http://localhost/Saft/TestGraph/> {<http://saft/test/s2> <http://saft/test/p2> '.
            '"43"^^<http://www.w3.org/2001/XMLSchema#string> . }',
            trim(
                $sparqlUtils->statementIteratorToSparqlFormat(array($triple, $quad), $this->testGraph)
            )
        );
    }

    public function testStatementIteratorToSparqlFormatTripleAndQuad()
    {
        $sparqlUtils = new SparqlUtils(new StatementIteratorFactoryImpl());

        $triple = new StatementImpl(
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s1'),
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/p1'),
            new LiteralImpl(new NodeUtils(), '42')
        );

        $quad = new StatementImpl(
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/s1'),
            new NamedNodeImpl(new NodeUtils(), 'http://saft/test/p1'),
            new LiteralImpl(new NodeUtils(), '42'),
            $this->testGraph
        );

        $this->assertEquals(
            '<http://saft/test/s1> <http://saft/test/p1> "42"^^<http://www.w3.org/2001/XMLSchema#string> .  '.
            'Graph <http://localhost/Saft/TestGraph/> {<http://saft/test/s1> <http://saft/test/p1> '.
            '"42"^^<http://www.w3.org/2001/XMLSchema#string> . }',
            trim($sparqlUtils->statementIteratorToSparqlFormat(array($triple, $quad)))
        );
    }
}
