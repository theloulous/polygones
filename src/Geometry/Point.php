<?php

namespace Geometry;

class Point
{
    // axe X
    private $abscissa;

    // axe Y
    private $ordinate;

    public function __construct(array $coordonnees)
    {
        $this->abscissa = $coordonnees[0];
        $this->ordinate = $coordonnees[1];
    }

    public function getAbscissa()
    {
        return $this->abscissa;
    }

    public function getOrdinate()
    {
        return $this->ordinate;
    }

    public function isEqual($point)
    {
        return bccomp($this->abscissa, $point->abscissa, 10) == 0 && bccomp($this->ordinate, $point->ordinate, 10) == 0;
    }

    public function isStrictlyHigher($point) : bool
    {
        return $this->ordinate > $point->ordinate;
    }

    public function isLower($point) : bool
    {
        return $this->ordinate <= $point->ordinate;
    }

    public function toArray()
    {
        return [
            $this->abscissa,
            $this->ordinate
        ];
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
