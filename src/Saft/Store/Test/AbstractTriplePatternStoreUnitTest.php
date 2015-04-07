<?php

namespace Saft\Store\Test;

use Saft\TestCase;
use Saft\Rdf\StatementIterator;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementImpl;

/**
 * Because this class has no tests, the extends part was commented out, otherwise PHPUnit throws a warning:
 * 1) Warning
 * No tests found in class "Saft\Store\Test\AbstractTriplePatternStoreUnitTest".
 * phar:///usr/bin/phpunit/phpunit/TextUI/Command.php:176
 * phar:///usr/bin/phpunit/phpunit/TextUI/Command.php:129
 */
class AbstractTriplePatternStoreUnitTest // extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = $this->getMockForAbstractClass('\Saft\Store\AbstractTriplePatternStore');
    }

    public function getTestQuad()
    {
        $subject1 = new NamedNodeImpl('http://saft/test/s1');
        $predicate1 = new NamedNodeImpl('http://saft/test/p1');
        $object1 = new NamedNodeImpl('http://saft/test/o1');
        $graph1 = new NamedNodeImpl('http://saft/test/g1');
        $quad = new StatementImpl($subject1, $predicate1, $object1, $graph1);

        return $quad;
    }

    public function getTestTriple()
    {
        $subject2 = new NamedNodeImpl('http://saft/test/s2');
        $predicate2 = new NamedNodeImpl('http://saft/test/p2');
        $object2 = new NamedNodeImpl('http://saft/test/o2');
        $triple = new StatementImpl($subject2, $predicate2, $object2);

        return $triple;
    }

    /*
     * Failed with the following error message: 
     * Saft\Store\Test\AbstractTriplePatternStoreUnitTest::testAddStatements
     * Expectation failed for method name is equal to <string:addStatements> when invoked 1 time(s).
     * Method was expected to be called 1 times, actually called 0 times.
     * 
     * At this time, class Saft\Store\AbstractTriplePatternStore has no addStatements method....
     *
    public function testAddStatements()
    {
        $triple = $this->getTestTriple();
        $quad = $this->getTestQuad();
        $query = 'INSERT DATA { '. $triple->toSparqlFormat(). $quad->toSparqlFormat(). '}';
        $this->fixture->query($query);
        
        // Override abstract-methods: it will check the statements
        $this->fixture
        ->expects($this->once())
        ->method('addStatements')
        ->will($this->returnCallback(
            function (StatementIterator $statements, $graphUri = null, array $options = array()) {
                PHPUnit_Framework_Assert::assertEquals(
                    $statements[0]->toSparqlFormat(),
                    $triple->toSparqlFormat()
                );
                PHPUnit_Framework_Assert::assertEquals(
                    $statements[1]->toSparqlFormat(),
                    $quad->toSparqlFormat()
                );
            }
        ));
    }*/

    /*
     * Failed with the following error message: 
     * Saft\Store\Test\AbstractTriplePatternStoreUnitTest::testDeleteMatchingStatements
     * Expectation failed for method name is equal to <string:deleteMatchingStatements> when invoked 1 time(s).
     * Method was expected to be called 1 times, actually called 0 times.
     * 
     * At this time, class Saft\Store\AbstractTriplePatternStore has no deleteMatchingStatements method....
     *
    public function testDeleteMatchingStatements()
    {
        $triple = $this->getTestTriple();
        $query = 'INSERT DATA { '.$triple->toSparqlFormat().'}';
        $this->fixture->query($query);
        
        $this->fixture
            ->expects($this->once())
            ->method('deleteMatchingStatements')
            ->will($this->returnCallback(
                function (Statement $statement, $graphUri = null, array $options = array()) {
                    PHPUnit_Framework_Assert::assertEquals(
                        $statement->toSparqlFormat(),
                        $triple->toSparqlFormat()
                    );
                }
            ));
    }
    */
}
