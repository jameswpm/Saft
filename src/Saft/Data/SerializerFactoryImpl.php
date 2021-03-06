<?php

namespace Saft\Data;

/**
 * @deprecated
 */
class SerializerFactoryImpl implements SerializerFactory
{
    /**
     * Creates a Serializer instance for a given serialization, if available.
     *
     * @param  string     $serialization The serialization you need a serializer for. In case it is not
     *                                   available, an exception will be thrown.
     * @return Parser     Suitable serializer for the requested serialization.
     * @throws \Exception If serializer for requested serialization is not available.
     */
    public function createSerializerFor($serialization)
    {
        throw new \Exception(
            'Class marked deprecated. Please use Saft\Skeleton\Data\SerializerFactory from now on.'
        );
    }

    /**
     * Returns a list of all supported serialization types.
     *
     * @return array Array of supported serialization types which can be used by this serializer.
     */
    public function getSupportedSerializations()
    {
        return array('n-triples', 'n-quads');
    }
}
