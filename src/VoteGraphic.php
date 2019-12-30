<?php

namespace VoteResultWidget;

use GDText\Box;
use GDText\Color;
use Illuminate\Support\Collection;

/**
 * Class Canvas
 *
 * A simple wrapper around the concept of an image resource in PHP.
 * The canvas represents the image and all its child resources (text, images, points, etc)
 *
 * It is the canvas in which you paint your beautiful congressional picture.
 *
 * @package VoteResultWidget
 */
class VoteGraphic
{
    /**
     * Width
     *
     * @var int The chart width, in pixels
     */
    public $width;

    /**
     * Height
     *
     * @var int The chart height, in pixels
     */
    public $height;

    /**
     * Canvas
     *
     * The canvas represents the entire image and all its children resources (images, text, points).
     * It is the canvas in which you paint your beautiful congressional picture.
     *
     * @var resource|false an image resource identifier on success, false on errors.
     */
    public $canvas;

    /**
     * Point Diameter
     *
     * @var int The diameter of a point representing a vote, in pixels
     */
    public $pointDiameter = 6;

    public function __construct($options = null)
    {
        $this->width = data_get($options, 'width', 250);
        $this->height = data_get($options, 'height', 250);

        // Create canvas rectangle
        $this->canvas = imagecreatetruecolor($this->width, $this->height);
        $this->createBackgroundCanvas();
    }

    /**
     * Create Background Canvas
     *
     * Creates the initial canvas for the graphic, based on set width and height.
     *
     * @return $this
     */
    public function createBackgroundCanvas()
    {
        imagefilledrectangle(
            $this->canvas,
            0, 0,
            $this->width, $this->height,
            ImageColorAllocate($this->canvas, 255, 255, 255)
        );

        return $this;
    }

    /**
     * Draw Title
     *
     * Draws the main title for the graphic (eg VOTE PASSED)
     *
     * @param $titleText string
     * @return $this
     */
    public function drawTitle($titleText)
    {
        $fontPath = dirname(__FILE__) . '/../data/NotoSans-Bold.ttf';
        $voteLabel = new Box($this->canvas);
        $voteLabel->setFontFace($fontPath);
        $voteLabel->setFontColor(new Color(53, 53, 53));
        $voteLabel->setFontSize(30);
        $voteLabel->setBox(0, 0, $this->width, $this->height);
        $voteLabel->setTextAlign('center', 'top');
        $voteLabel->draw($titleText);

        return $this;
    }

    /**
     * Draw Progress Bar
     *
     * Draws a progress bar representing the vote, and how close it was to passing.
     *
     * @param $activeColorRgb int The integer returned by `imagecolorallocate` representing a color
     * @param $activePixelWidth float The pixels representing the active fill color
     * @param $votePercentageRequired int The percent of votes required for a vote passage
     * @return $this
     */
    public function drawProgressBar($activeColorRgb, $activePixelWidth, $votePercentageRequired)
    {
        // Background box for progress bar
        $voteFillBgColor = imagecolorallocate($this->canvas, 248, 250, 252);
        $this->createRoundedRectangle(
            $this->canvas,
            0, 52,    // Start x,y
            $this->width, 82,  // End x,y
            5,
            $voteFillBgColor
        );

        // Active fill color for progress bar
        $this->createRoundedRectangle(
            $this->canvas,
            0, 52,    // Start x,y
            $activePixelWidth, 82,  // End x,y
            5,
            $activeColorRgb
        );

        /**
         * Draw divider, representing a minimum vote needed for passage
         */
        $dividerPng = imagecreatefrompng( dirname(__FILE__) . '/../data/minimum-vote-divider.png');
        $dividerXValue = $votePercentageRequired * $this->width;
        imagecolortransparent($dividerPng, imagecolorallocate($dividerPng, 173, 173, 173));
        imagecopy(
            $this->canvas,
            $dividerPng,
            $dividerXValue, 47,
            0, 0,
            1, 39
        );

        /**
         * Draw Emoji
         */
        $thumbupEmoji = imagecreatefrompng( dirname(__FILE__) . '/../data/emoji-thumbup-whitebg.png');
        $emojiXValue = $activePixelWidth - 20;
        if ($emojiXValue >= ($this->width - 20)) {
            $emojiXValue = $this->width - 40;
        } else if ($emojiXValue < 0) {
            $emojiXValue = 0;
        }
        imagecolortransparent($thumbupEmoji, imagecolorallocate($thumbupEmoji, 173, 173, 173));
        imagecopy(
            $this->canvas,
            $thumbupEmoji,
            $emojiXValue, 47,
            0, 0,
            41, 41
        );

        return $this;
    }

    /**
     * Draw Vote Count Label
     *
     * Draws a simple `xx - xx` label in the middle of the graphic, representing vote counts.
     *
     * @param $labelText string
     * @return $this
     */
    public function drawVoteCountLabel($labelText)
    {
        $fontPath =  dirname(__FILE__) . '/../data/NotoSans-Bold.ttf';
        $voteLabel = new Box($this->canvas);
        $voteLabel->setFontFace($fontPath);
        $voteLabel->setFontColor(new Color(53, 53, 53));
        $voteLabel->setFontSize(32);
        $voteLabel->setBox(0, 125, $this->width, $this->height);
        $voteLabel->setTextAlign('center', 'top');
        $voteLabel->draw($labelText);

        return $this;
    }

    /**
     * Create Rounded Rectangle
     *
     * A helper method to draw a rounded rectangle in GD - which is harder than you'd think!
     * Sorry for the mess in parameter-hell, I found this on StackOverflow and didn't care to refactor it.
     *
     * Such is life.
     *
     * @param $im
     * @param $x
     * @param $y
     * @param $cx
     * @param $cy
     * @param $rad
     * @param $col
     * @return $this
     */
    function createRoundedRectangle(&$im, $x, $y, $cx, $cy, $rad, $col)
    {
        // Draw the middle cross shape of the rectangle
        imagefilledrectangle($im, $x, $y + $rad, $cx, $cy - $rad, $col);
        imagefilledrectangle($im, $x + $rad, $y, $cx - $rad, $cy, $col);

        $dia = $rad * 2;

        // Now fill in the rounded corners
        imagefilledellipse($im, $x + $rad, $y + $rad, $rad * 2, $dia, $col);
        imagefilledellipse($im, $x + $rad, $cy - $rad, $rad * 2, $dia, $col);
        imagefilledellipse($im, $cx - $rad, $cy - $rad, $rad * 2, $dia, $col);
        imagefilledellipse($im, $cx - $rad, $y + $rad, $rad * 2, $dia, $col);

        return $this;
    }

    /**
     * Draw Arc Slice on Canvas
     *
     * Draws a slice of the arc on the canvas.
     *
     * @param Collection $arcPoints
     * @param $xPadding int The X padding of the slice
     * @param $yPadding int The Y padding of the slice
     * @return $this
     */
    public function drawArcSliceOnCanvas(Collection $arcPoints, $xPadding, $yPadding)
    {
        foreach ($arcPoints as $arcPoint) {
            $angle = $arcPoint->angle * (180 / pi());
            $angle = 360 - $angle;
            if ((int)$angle === 360) {
                $angle = 90;
            }

            // set background
            $dotImage = imagecreatetruecolor($this->pointDiameter, $this->pointDiameter);
            imagefill($dotImage, 0, 0, ImageColorAllocate($this->canvas, 255, 255, 255));

            // filled square
            imagefilledpolygon(
                $dotImage,
                [
                    0, 0,
                    0, $this->pointDiameter,
                    $this->pointDiameter, $this->pointDiameter,
                    $this->pointDiameter, 0
                ],
                4,
                ImageColorAllocate($dotImage, $arcPoint->color[0], $arcPoint->color[1], $arcPoint->color[2])
            );
            $dotImage = imagerotate($dotImage, $angle, ImageColorAllocate($this->canvas, 255, 255, 255));
            imagecolortransparent($dotImage, imagecolorallocate($dotImage, 0, 0, 0));

            imagecopy(
                $this->canvas,
                $dotImage,
                $arcPoint->x + $xPadding, $arcPoint->y + $yPadding,
                0, 0,
                $this->pointDiameter * 2, $this->pointDiameter * 2
            );
            imagedestroy($dotImage);
        }

        return $this;
    }
}
