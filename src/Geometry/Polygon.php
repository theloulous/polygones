<?php
namespace Geometry;

use Collection\Collection;
use function Math\max;
use function Math\min;

class Polygon
{
    /**
     * @var Collection
     */
    private $segments;

    public function __construct(array $pointsListe)
    {
        $lengthListe = count($pointsListe);

        if (
            $lengthListe <= 3
            || end($pointsListe) !== reset($pointsListe)
        ) {
            throw new \Exception('Not a polygon');
        }

        $this->segments = new Collection();

        for ($i = 0; $i < $lengthListe - 1; ++$i) {
            $pointA           = new Point(current($pointsListe));
            $pointB           = new Point(next($pointsListe));
            $this->segments[] = new Segment($pointA, $pointB);
            unset($pointA, $pointB);
        }
    }

    public function getSegments()
    {
        return $this->segments;
    }

    public function getBoundingbox()
    {
        $latmin = $latmax = $lgtmin = $lgtmax = null;

        foreach ($this->segments as $segment) {
            $lgtmin = min($lgtmin, $segment->getPointA()->getAbscissa());
            $lgtmax = max($lgtmax, $segment->getPointA()->getAbscissa());
            $latmin = min($latmin, $segment->getPointA()->getOrdinate());
            $latmax = max($latmax, $segment->getPointA()->getOrdinate());
        }

        return [
            [$latmax, $lgtmax],
            [$latmin, $lgtmin],
        ];
    }

    public function containsPoint(Point $point)
    {
        $wn = 0;

        /** @var \Geometry\Segment $segment */
        foreach ($this->segments as $segment) {
            if ($segment->hasForEndPoint($point) || $segment->containsPoint($point)) {
                return false;
            }
            if ($segment->getPointA()->isLower($point)) {
                if ($segment->getPointB()->isStrictlyHigher($point) && $this->isLeft($segment, $point) > 0) {
                    ++$wn;
                }
            } else {
                if ($segment->getPointB()->isLower($point) && $this->isLeft($segment, $point) < 0) {
                    --$wn;
                }
            }
        }

        return $wn != 0;
    }

    /**
     * Calcul du determinant entre le vertex et le vertex formé du point d'origne du vertex et le point.
     * @param  Segment $segment [description]
     * @param  Point   $point  [description]
     * @return boolean         [description]
     */
    private function isLeft(Segment $segment, Point $point)
    {
        $segmentSecond = new Segment($segment->getPointA(), $point);
        return determinant($segment, $segmentSecond);
    }

    public function getBarycenter()
    {
        $total              = 0;
        $abscissaBarycenter = 0;
        $ordinateBarycenter = 0;

        foreach ($this->segments as $segment) {
            $total++;
            $abscissaBarycenter += $segment->getPointA()->getAbscissa();
            $ordinateBarycenter += $segment->getPointA()->getOrdinate();
        }

        return new Point([$abscissaBarycenter / $total, $ordinateBarycenter / $total]);
    }

    public function getAllSegmentsIntersectionWith($polygonContender)
    {
        $mySegments  = clone $this->segments;
        $hisSegments = clone $polygonContender->segments;

        foreach ($mySegments as $myKey => $mySegment) {
            foreach ($hisSegments as $hisKey => $hisSegment) {
                $newSegments = $mySegment->getPartitionsbySegment($hisSegment);
                if ($newSegments) {
                    $mySegments->insert($myKey, $newSegments);
                    $mySegment = $newSegments[0];
                }

                $newSegments = $hisSegment->getPartitionsbySegment($mySegment);
                if ($newSegments) {
                    $hisSegments->insert($hisKey, $newSegments);
                    $hisSegment = $newSegments[0];
                }

                if ($mySegment->isEqual($hisSegment)) {
                    $hisSegments->delete($hisKey);
                    if ($mySegment->isBetweenPolygons($this, $polygonContender)) {
                        $mySegments->delete($myKey);
                        break;
                    }
                }
            }
        }

        $allSegments = (new Collection())
            ->append($mySegments)
            ->append($hisSegments)
        ;

        $resultSegments = new Collection();

        foreach ($allSegments as $segment) {
            $middlePoint = $segment->getMiddlePoint();
            if ($this->containsPoint($middlePoint) || $polygonContender->containsPoint($middlePoint)) {
                continue;
            }

            $resultSegments[] = $segment;
        }

        return $resultSegments;
    }

    public static function buildFromSegments(Collection $segments)
    {
        if ($segments->getType() !== Segment::class) {
            throw new \Exception('Argument is not a Collection of Segment');
        }

        $segments->rewind();
        $point        = $segments->current()->getPointA();
        $pointOrigine = $point;

        $points       = [];
        $points[]     = $point;
        $newPolygones = new Collection;

        while ($segments->count()) {
            $segment = $segments->current();
            $key     = $segments->key();

            $segments->next();
            if ($segment->hasForEndPoint($point)) {
                $point = $segment->getOtherPoint($point);
                unset($segments[$key]);

                if ($pointOrigine->isEqual($point)) {
                    $ptListe = '';
                    foreach ($points as $pt) {
                        $ptListe .= $pt->toJSON();
                        $ptListe .= ',';
                    }
                    $ptListe .= $pointOrigine->toJSON();
                    $newPolygones[] = new Polygon(json_decode('['.$ptListe.']'));

                    if ($segments->count()) {
                        $newPolygones->append(
                            self::buildFromSegments($segments)
                        );
                        $segments = new Collection;
                    }
                }
                $points[] = $point;
                $segments->rewind();
            }
        }
        return $newPolygones;
    }

    public function union($polygonContender)
    {
        return self::buildFromSegments(
            $this->getAllSegmentsIntersectionWith($polygonContender)
        );
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->segments as $segment) {
            $array[] = $segment->getPointA()->toArray();
        }
        $array[] = $this->segments[0]->getPointA()->toArray();
        return $array;
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
