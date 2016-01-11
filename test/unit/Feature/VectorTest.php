<?php

namespace Choult\Enamel\Test\Feature;

use \Choult\Enamel\Feature\Vector;

/**
 * A class to represent a vector
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 *
 * @coversDefaultClass \Choult\Enamel\Feature\Vector
 */
class VectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * DataProvider for testConstruct
     *
     * @return array
     */
    public function constructProvider()
    {
        return [
            'All numeric' => [
                'data' => [
                    'feature1' => 2,
                    'feature2' => 4.2
                ]
            ],
            'Not all numeric' => [
                'data' => [
                    'feature1' => 2,
                    'feature2' => 'a'
                ],
                'expectedException' => '\Choult\Enamel\Feature\Vector\Exception'
            ]
        ];
    }

    /**
     * @dataProvider constructProvider
     *
     * @covers ::__construct
     * @covers ::getFeatures
     * @covers ::testOneD
     *
     * @param array $data
     * @param string    $expectedException  If set, the class of Exception to expect
     */
    public function testConstruct(array $data, $expectedException = '')
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $vector = new Vector($data);
        $this->assertEquals($data, $vector->getFeatures());
    }
}
