<?php

namespace Saft\Data\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementImpl;
use Saft\Test\TestCase;

abstract class ParserAbstractTest extends TestCase
{
    /**
     * @return Parser
     */
    abstract protected function newInstance($serialization);

    /*
     * Tests for __construct
     */

    public function testConstructorInvalidSerialization()
    {
        $this->setExpectedException('\Exception');

        $this->newInstance('unknown');
    }

    /*
     * Tests for parseStreamToIterator
     * we load here the content of a file and transform it into an StatementIterator instance.
     * afterwards we check if the read data are the same as expected.
     */

    public function testParseStreamToIteratorTurtleFile()
    {
        $this->fixture = $this->newInstance('turtle');

        // load iterator for a turtle file
        $inputStream = dirname(__FILE__) .'/../resources/example.ttl';
        $iterator = $this->fixture->parseStreamToIterator($inputStream);

        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foo')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(new NodeUtils(), 'RDFS label')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foobar'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foobar'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    new NodeUtils(),
                    'RDFS label with language tag',
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString'),
                    'en'
                )
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foobar'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#comment'),
                new LiteralImpl(new NodeUtils(), "\n    Multi line comment\n    ")
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foobar'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/component'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/geo')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foobar'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/component'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/time')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/dataset'),
                new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2000/01/rdf-schema#label'),
                new LiteralImpl(
                    new NodeUtils(),
                    "RDFS label with datatype",
                    new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#string')
                )
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $iterator);
    }

    /*
     * Tests for parseStringToIterator
     */

    public function testParseStringToIteratorTurtleString()
    {
        $xsdString = new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance('turtle');

        $testString = '@prefix ex: <http://saft/example/> .
            ex:Foo  ex:knows ex:Bar ; ex:name  "Foo"^^<'. $xsdString .'> .
            ex:Bar  ex:name  "Bar"^^<'. $xsdString .'> .';

        // build StatementIterator to check against
        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foo'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/knows'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Bar')
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Foo'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/name'),
                new LiteralImpl(new NodeUtils(), 'Foo', $xsdString)
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Bar'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/name'),
                new LiteralImpl(new NodeUtils(), 'Bar', $xsdString)
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $fixture->parseStringToIterator($testString));
    }

    // that test checks if the subject can be of type blank node
    public function testParseStringToIteratorTurtleStringSubjectBlankNode()
    {
        $xsdString = new NamedNodeImpl(new NodeUtils(), 'http://www.w3.org/2001/XMLSchema#string');

        $fixture = $this->newInstance('turtle');

        $testString = '@prefix ex: <http://saft/example/> .
            _:foo  ex:knows ex:Bar ; ex:name  "Foo"^^<'. $xsdString .'> .
            ex:Bar  ex:name  "Bar"^^<'. $xsdString .'> .';

        // build StatementIterator to check against
        $statementIteratorToCheckAgainst = new ArrayStatementIteratorImpl(array(
            new StatementImpl(
                new BlankNodeImpl('_:genid1'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/knows'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Bar')
            ),
            new StatementImpl(
                new BlankNodeImpl('_:genid1'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/name'),
                new LiteralImpl(new NodeUtils(), 'Foo', $xsdString)
            ),
            new StatementImpl(
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/Bar'),
                new NamedNodeImpl(new NodeUtils(), 'http://saft/example/name'),
                new LiteralImpl(new NodeUtils(), 'Bar', $xsdString)
            ),
        ));

        $this->assertEquals($statementIteratorToCheckAgainst, $fixture->parseStringToIterator($testString));
    }
}
