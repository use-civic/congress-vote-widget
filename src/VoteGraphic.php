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
    public $width;

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

    public $pointDiameter = 3;

    public function __construct($options = null)
    {
        $this->width = data_get($options, 'width', 250);
        $this->height = data_get($options, 'height', 250);

        // Create canvas rectangle
        $this->canvas = imagecreatetruecolor($this->width, $this->height);
        $this->createBackgroundCanvas();
    }

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

    public function drawTitle($titleText)
    {
        $fontPath = ROOTPATH . '/data/NotoSans-Bold.ttf';
        $voteLabel = new Box($this->canvas);
        $voteLabel->setFontFace($fontPath);
        $voteLabel->setFontColor(new Color(53, 53, 53));
        $voteLabel->setFontSize(30);
        $voteLabel->setBox(0, 0, $this->width, $this->height);
        $voteLabel->setTextAlign('center', 'top');
        $voteLabel->draw($titleText);
    }

    public function drawProgressBar($activeColorRgb, $activePixelWidth)
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
         * Draw Emoji
         */
        $thumbupEmoji = imagecreatefrompng(ROOTPATH . '/data/emoji-thumbup-whitebg.png');
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

    public function drawVoteCountLabel($labelText)
    {
        $fontPath = ROOTPATH . '/data/NotoSans-Bold.ttf';
        $voteLabel = new Box($this->canvas);
        $voteLabel->setFontFace($fontPath);
        $voteLabel->setFontColor(new Color(53, 53, 53));
        $voteLabel->setFontSize(26);
        $voteLabel->setBox(0, 100, $this->width, $this->height);
        $voteLabel->setTextAlign('center', 'top');
        $voteLabel->draw($labelText);
    }

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
