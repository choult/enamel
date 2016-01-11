<?php

namespace Choult\Enamel\Feature;

use \Choult\Enamel\Feature\Vector\Exception;

/**
 * A class to represent a vector
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
class Vector
{
    /**
     * @var array   An array of features, indexed by feature name
     */
    private $data;

    /**
     * Constructs a new Feature Vector
     *
     * @param array $data   An array of features, indexed by feature name
     */
    public function __construct(array $data)
    {
        $this->testOneD($data);
        $this->data = $data;
    }

    /**
     * Gets an array of features, indexed by feature name
     *
     * @return array    An array of features, indexed by feature name
     */
    public function getFeatures()
    {
        return $this->data;
    }

    /**
     * Tests whether the passed array is composed solely of numeric values
     *
     * @param array $data   An array of features, indexed by feature name
     *
     * @throws \Choult\Enamel\Feature\Vector\Exception
     */
    private function testOneD(array $data)
    {
        array_walk(
            $data,
            function ($item) {
                if (!is_numeric($item)) {
                    throw new Exception('A vector may only consist of numeric values');
                }
            }
        );
    }
}
