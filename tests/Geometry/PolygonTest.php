<?php
namespace Geometry;

use Collection\Collection;
use PHPUnit\Framework\TestCase;

class PolygonTest extends TestCase
{
    public function instanceProvider()
    {
        return [
            [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]],
            [[[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]]],
        ];
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testNewInstance($pointsListe) {
        $instance = new Polygon($pointsListe);

        self::assertInstanceOf(Polygon::class, $instance);
    }

    public function failInstanceProvider()
    {
        return [
            [[]],
            [[[1, 1], [1, 5]]],
            [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 1]]],
            [[[82.9562, 98.729], [52.16232, 12.5954], [95.491569, 14.434342], [46.718, 98.5924]]],
        ];
    }

    /**
     * @dataProvider failInstanceProvider
     */
    public function testFailInstanceProvider($pointsListe) {
        $this->expectException(\Exception::class);

        $instance = new Polygon($pointsListe);
        self::assertInstanceOf(Polygon::class, $instance);
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testGetSegments($pointsListe) {
        $instance = new Polygon($pointsListe);

        self::assertInstanceOf(Collection::class, $instance->getSegments());
    }

    public function providerGetBoundingBox()
    {
        return [
            [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]], [[5, 5], [0, 0]]],
            [[[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]], [[5, 5], [1, 1]]],
        ];
    }

    /**
     * @dataProvider providerGetBoundingBox
     */
    public function testGetBoundingBox($pointsListe, $boudingBox) {
        $instance = new Polygon($pointsListe);

        self::assertEquals($boudingBox, $instance->getBoundingBox());
    }

    public function providerContainsPoint()
    {
        return [
            [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]], [4, 4], true],
            [[[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]], [6, 6], false],
            [[[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]], [5, 3], false],
        ];
    }

    /**
     * @dataProvider providerContainsPoint
     */
    public function testContainsPoint($polygonCoordinates, $pointCoordinates, $expected)
    {
        $instance = new Polygon($polygonCoordinates);
        $point = new Point($pointCoordinates);
        self::assertEquals($expected, $instance->containsPoint($point));
    }

    public function providerGetAllSegmentsIntersectionWith()
    {
        return [
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [[10, 10], [10, 15], [15, 15], [15, 10], [10, 10]],
                [
                    [[0, 0], [0, 5]],
                    [[0, 5], [5, 5]],
                    [[5, 5], [5, 0]],
                    [[5, 0], [0, 0]],
                    [[10, 10], [10, 15]],
                    [[10, 15], [15, 15]],
                    [[15, 15], [15, 10]],
                    [[15, 10], [10, 10]],
                ],
            ],
            [
                [[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]],
                [[3, 0], [3, 6], [7, 6], [7, 0], [3, 0]],
                [
                    [[1, 1], [1, 5]],
                    [[1, 5], [3, 5]],
                    [[3, 1], [1, 1]],
                    [[3, 0], [3, 1]],
                    [[3, 5], [3, 6]],
                    [[3, 6], [7, 6]],
                    [[7, 6], [7, 0]],
                    [[7, 0], [3, 0]],
                ],
            ],
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]],
                [
                    [[0, 0], [0, 5]],
                    [[0, 5], [1, 5]],
                    [[1, 5], [5, 5]],
                    [[5, 5], [5, 1]],
                    [[5, 1], [5, 0]],
                    [[5, 0], [0, 0]],
                ],
            ],
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [[10, 0], [10, 5], [5, 5], [5, 0], [10, 0]],
                [
                    [[0, 0], [0, 5]],
                    [[0, 5], [5, 5]],
                    [[5, 0], [0, 0]],
                    [[10, 0], [10, 5]],
                    [[10, 5], [5, 5]],
                    [[5, 0], [10, 0]],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerGetAllSegmentsIntersectionWith
     */
    public function testGetAllSegmentsIntersectionWith($polygonACoordinates, $polygonBCoordinates, $expectedCoordinates)
    {
        $polygonA = new Polygon($polygonACoordinates);
        $polygonB = new Polygon($polygonBCoordinates);

        $segments = $polygonA->getAllSegmentsIntersectionWith($polygonB);

        self::assertInstanceOf(Collection::class, $segments);

        $json = [];
        foreach ($segments as $key => $segment) {
            $json[] = [
                [$segment->getPointA()->getAbscissa(), $segment->getPointA()->getOrdinate()],
                [$segment->getPointB()->getAbscissa(), $segment->getPointB()->getOrdinate()],
            ];
        }

        self::assertEquals($expectedCoordinates, $json, json_encode($json));
    }

    public function providerGetBarycenter()
    {
        return [
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [2.5, 2.5]
            ],
            [
                [[4, 4], [2, 2], [3, 1], [4, 4]],
                [3, 2.3333333333333335]
            ]
        ];
    }

    /**
     * @dataProvider providerGetBarycenter
     */
    public function testGetBarycenter($polygonCoordinates, $expectedPointCoordinates)
    {
        $polygon = new Polygon($polygonCoordinates);
        $point   = new Point($expectedPointCoordinates);

        $barycenter = $polygon->getBarycenter();

        self::assertInstanceOf(Point::class, $barycenter);
        self::assertEquals($point->toJSON(), $barycenter->toJSON());
    }

    public function providerUnion()
    {
        return [
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [[1, 1], [1, 5], [5, 5], [5, 1], [1, 1]],
                [
                    [[0, 0], [0, 5], [1, 5], [5, 5], [5, 1], [5, 0], [0, 0]],
                ],
            ],
            [
                [[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]],
                [[1, 1], [1, 5], [4, 5], [4, 1], [1, 1]],
                [
                    [[0, 0], [0, 5], [1, 5], [4, 5], [5, 5], [5, 0], [0, 0]],
                ],
            ],
            [
                [[1, 1], [2, 2], [3, 1], [1, 1]],
                [[0, 1], [2, 2], [3, 1], [0, 1]],
                [
                    [[2, 2], [3, 1], [1, 1], [0, 1], [2, 2]],
                ],
            ],
            [
                [[5, 5], [2, 2], [3, 1], [5, 5]],
                [[4, 4], [2, 2], [3, 1], [4, 4]],
                [
                    [[5, 5], [4, 4], [2, 2], [3, 1], [5, 5]],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerUnion
     */
    public function testUnion($polygonACoordinates, $polygonBCoordinates, $expectedPolygonsCoordinates)
    {
        $polygonA = new Polygon($polygonACoordinates);
        $polygonB = new Polygon($polygonBCoordinates);

        $json = [];
        foreach ($polygonA->union($polygonB) as $polygon) {
            $json[] = json_decode($polygon->toJSON());
        }

        self::assertEquals($expectedPolygonsCoordinates, $json, json_encode($json));
    }
}
