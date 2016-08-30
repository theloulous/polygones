<?php

namespace GeometryTest;

use Collection\Collection;
use Geometry\Polygon;
use Geometry\Point;

class PolygonTest extends \PHPUnit_Framework_TestCase
{
    public function instanceProvider()
    {
        return [
			[[[0,0],[0,5],[5,5],[5,0],[0,0]]],
			[[[1,1],[1,5],[5,5],[5,1],[1,1]]],
        ];
    }

	/**
	 * @dataProvider instanceProvider
	 */
	public function testNewInstance($pointsListe) {
		$instance = new Polygon($pointsListe);

		$this->assertInstanceOf(Polygon::class, $instance);
	}

    public function failInstanceProvider()
    {
        return [
			[[]],
			[[[1,1],[1,5]]],
			[[[0,0],[0,5],[5,5],[5,0],[0,1]]],
        ];
    }

	/**
	 * @dataProvider failInstanceProvider
	 */
	public function testFailInstanceProvider($pointsListe) {
		$this->expectException(\Exception::class);

		$instance = new Polygon($pointsListe);
		$this->assertInstanceOf(Polygon::class, $instance);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetSegments($pointsListe) {
		$instance = new Polygon($pointsListe);

		$this->assertInstanceOf(Collection::class, $instance->getSegments());
	}

	public function providerGetBoundingBox()
    {
        return [
			[[[0,0], [0,5], [5,5], [5,0], [0,0]], [[5, 5], [0, 0]]],
			[[[1,1], [1,5], [5,5], [5,1], [1,1]], [[5, 5], [1, 1]]],
        ];
    }

	/**
	 * @dataProvider providerGetBoundingBox
	 */
	public function testGetBoundingBox($pointsListe, $boudingBox) {
		$instance = new Polygon($pointsListe);

		$this->assertEquals($boudingBox, $instance->getBoundingBox());
	}

	public function providerContainsPoint()
	{
		return [
			[[[0,0], [0,5], [5,5], [5,0], [0,0]], [4,4], true],
			[[[1,1], [1,5], [5,5], [5,1], [1,1]], [6,6], false],
			[[[1,1], [1,5], [5,5], [5,1], [1,1]], [5,3], false],
		];
	}

	/**
	 * @dataProvider providerContainsPoint
	 */
	public function testContainsPoint($polygonCoordinates, $pointCoordinates, $expected)
	{
		$instance 	= new Polygon($polygonCoordinates);
		$point 		= new Point($pointCoordinates);
		$this->assertEquals($expected, $instance->containsPoint($point));
	}

	public function providerGetAllSegmentsIntersectionWith()
	{
		return [
			[
                [[0,0], [0,5], [5,5], [5,0], [0,0]],
                [[10,10], [10,15], [15,15], [15,10], [10,10]],
                [
                    [0,0], [0,5], [5,5], [5,0], [10,10], [10,15], [15,15], [15,10],
                ],
            ],
			[
                [[1,1], [1,5], [5,5], [5,1], [1,1]],
                [[3,0], [3,6], [7,6], [7,0], [3,0]],
                [
                    [1,1], [1,5], [3,5], [5,5], [5,1], [3,1], [3,0], [3,1], [3,5], [3,6], [7,6], [7,0]
                ],
            ],
		];
	}

	/**
	 * @dataProvider providerGetAllSegmentsIntersectionWith
	 */
	public function testGetAllSegmentsIntersectionWith($polygonACoordinates, $polygonBCoordinates, $expectedCoordinates)
	{
		$polygonA 	= new Polygon($polygonACoordinates);
		$polygonB   = new Polygon($polygonBCoordinates);

        $segments = $polygonA->getAllSegmentsIntersectionWith($polygonB);

        $json = [];
        foreach ($segments as $key => $segment) {
        	$json[] = [$segment->getPointA()->getAbscissa(), $segment->getPointA()->getOrdinate()];
        }
        
		$this->assertEquals($expectedCoordinates, $json);
	}
}
