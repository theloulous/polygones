<?php

namespace Geometry;

/**
* point
*/
class Point {

	// axe X
	private $abscissa;

	// axe Y
	private $ordinate;

	public function __construct(array $coordonnees) {
		$this->abscissa = $coordonnees[0];
		$this->ordinate = $coordonnees[1];
	}

	public function getAbscissa() {
		return $this->abscissa;
	}

	public function getOrdinate() {
		return $this->ordinate;
	}

	public function isEqual($point) {
		return bccomp($this->abscissa, $point->getAbscissa(), 10) == 0 && bccomp($this->ordinate, $point->getOrdinate(), 10) == 0;
	}

	public function isInsidePolygon($polygon) {
		return $polygon->containsPoint($this);
	}

	public function toJSON() {
		$json = "[".$this->abscissa.",".$this->ordinate."]";
		return $json;
	}
}
