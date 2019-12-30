<?php

namespace VoteResultWidget;

/**
 * Class Point
 *
 * A simple class to represent a point in math. Tracks the x, y, and angle values of the point.
 *
 * You can optionally set a color to represent party affiliation.
 *
 * @package VoteResultWidget
 */
class Point
{
    /**
     * X
     *
     * This marks the spot, so to speak, of the X value in 2d space.
     *
     * @var float
     */
    public $x = 0;

    /**
     * Y
     *
     * The Y coordinate, often paired with an X coordinate.
     *
     * @var float
     */
    public $y = 0;

    /**
     * Angle
     *
     * The angle of the point. Points are represented as squares, which point at the centroid.
     * As such, we set the angle to represent the angle we have to rotate the square to look at the center.
     *
     * @var float
     */
    public $angle = 0;

    /**
     * Color
     *
     * An array of RGB values. This is used to represent party affiliation.
     *
     * @var array
     */
    public $color = [0, 0, 0];

    /**
     * Diameter
     *
     * The pixel diameter of the point
     *
     * @var float
     */
    public $diameter = 3;

    /**
     * Is Filled
     *
     * When true, fill the point with the active color.
     * When false, a white inside is shown to indicate a nay/non-vote
     *
     * @var bool
     */
    public $isFilled = false;

    /**
     * Set Angle
     *
     * Sets the angle of the point
     *
     * @param $value
     * @return $this
     */
    public function setAngle($value)
    {
        $this->angle = $value;

        return $this;
    }

    /**
     * Set Diameter
     *
     * Sets the diameter
     *
     * @param $value
     * @return $this
     */
    public function setDiameter($value)
    {
        $this->diameter = $value;

        return $this;
    }

    /**
     * Set X
     *
     * Sets the X coordinate
     *
     * @param $value
     * @return $this
     */
    public function setX($value)
    {
        $this->x = $value;

        return $this;
    }

    /**
     * Set Y
     *
     * Sets the Y coordinate
     *
     * @param $value
     * @return $this
     */
    public function setY($value)
    {
        $this->y = $value;

        return $this;
    }

    /**
     * Get Diameter
     *
     * Gets the pixel diameter of the point
     *
     * @return float
     */
    public function getDiameter()
    {
        return $this->diameter;
    }

    /**
     * Get X
     *
     * Gets the X coordinate
     *
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Get Y
     *
     * Gets the Y Coordinate
     *
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Get Angle
     *
     * Gets the angle of the point
     *
     * @return float
     */
    public function getAngle()
    {
        return $this->angle;
    }
}
