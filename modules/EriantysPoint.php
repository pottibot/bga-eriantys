<?php

// copied from Eriantys
class EriantysPoint {
    
    private $x;
    private $y;

    public function __construct($x=0,$y=0) {
        $this->x = floatval($x);
        $this->y = floatval($y);
    }

    public function __toString() {
        return '('.$this->x.', '.$this->y.')';
    }

    // get x cooordinate
    public function x() {
        return round($this->x,5);
    }

    // get y cooordinate
    public function y() {
        return round($this->y,5);
    }

    // returns coordinates in array form and rounded values
    public function coordinates() {
        return array('x' => $this->x(), 'y' => $this->y());
    }

    // invert point coordinates
    public function invert() {
        return new self(-$this->x,-$this->y);
    }

    // applies simple counter-clockwise rotation to point coordinates
    public function rotate($the) {

        $c = cos($the);
        $s = sin($the);

        return new self($this->x*$c - $this->y*$s, $this->x*$s + $this->y*$c);
    }

    // applies simple translation to point coordinates
    public function translate($tx, $ty) {
        return new self($this->x + $tx, $this->y + $ty);
    }

    // applies translation using polar coordinates
    public function translatePolar($ro, $the) {
        return $this->translate($ro*cos($the), $ro*sin($the));
    }

    // creates 'vector' by translating origin (0,0) using polar coordinates
    public static function createPolarVector($ro, $the) {
        $o = new self();
        return $o->translate($ro*cos($the), $ro*sin($the));
    }

    // applies simple translation to point coordinates
    public function scale($sx, $sy) {

        return new self($this->x * $sx, $this->y * $sy);
    }

    // change reference origin and applies transformation (scale and rot), then return transformed point to previous reference origin
    public function transformFromOrigin(EriantysPoint $origin, $sx, $sy, $the = 0) {

        $centered = $this->translate(-$origin->x, -$origin->y);
        $scaled = $centered->scale($sx,$sy);
        $rotated = $scaled->rotate($the);
        
        return $rotated->translate($origin->x, $origin->y);
    }

    // calculates euclidean distance between point1 and point2
    public static function distance(EriantysPoint $p1, EriantysPoint $p2) {

        return sqrt(pow($p2->x - $p1->x, 2) + pow($p2->y - $p1->y, 2));
    }

    // find median midpoint between point1 and point2
    public static function midpoint(EriantysPoint $p1, EriantysPoint $p2) {

        $mx = ($p1->x + $p2->x)/2;
        $my = ($p1->y + $p2->y)/2;

        return new self($mx, $my);
    }

    public static function lerp(EriantysPoint $a, EriantysPoint $b, $t) {
        $cx = $t * ($b->x - $a->x) + $a->x;
        $cy = $t * ($b->y - $a->y) + $a->y;

        return new self($cx, $cy);
    }

    // calculates displacement vector between origin and end point
    public static function displacementVector(EriantysPoint $origin, EriantysPoint $point) {

        $vx = $point->x - $origin->x;
        $vy = $point->y - $origin->y;

        return new EriantysPoint($vx, $vy);
    }

    // calculates norm of point vector from origin
    public function normalize() {

        $mag = self::distance(new EriantysPoint(0,0), $this);

        return new self($this->x / $mag, $this->y / $mag);
    }

    // calculates dot product between two points
    public static function dot(EriantysPoint $v1, EriantysPoint $v2) {

        $d1 = $v1->x * $v2->x;
        $d2 = $v1->y * $v2->y;

        return $d1 + $d2;
    }
}