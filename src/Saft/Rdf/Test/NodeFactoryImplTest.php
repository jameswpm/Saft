<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;

class NodeFactoryImplTest extends NodeFactoryAbstractTest
{

    /**
     * An abstract method which returns new instances of NodeFactory
     */
    public function getFixture()
    {
        return new NodeFactoryImpl(new NodeUtils());
    }
}
