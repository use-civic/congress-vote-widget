<?php

namespace VoteResultWidget;

use Exception;
use Illuminate\Support\Collection;

/**
 * Class GraphicGenerator
 *
 * A manager-style class that mediates vote data to the underlying PHP GD graphic generation class.
 *
 * @package VoteResultWidget
 */
class GraphicGenerator
{
    /**
     * Graphic
     *
     * An instance of the VoteGraphic class.
     *
     * @var VoteGraphic
     */
    public $graphic;

    /**
     * Calculate Points on Arc for Slice
     *
     * Given information about the circle, arc, and votes - generate a single slice  for a circle.
     *
     * @param int $totalSlices Total slices to generate. Differs based on House/Senate
     * @param int $sliceIteration The slice we're currently generating for
     * @param Collection $votes A collection of votes
     * @param float $radius The circle radius to generate around
     * @param Point $circleCentroidPoint The centroid to generate around
     * @return Collection|\Tightenco\Collect\Support\Collection
     * @throws Exception When we can't read a vote property for a vote
     */
    public function calculatePointsOnArcForSlice($totalSlices, $sliceIteration, Collection $votes, $radius, Point $circleCentroidPoint)
    {
        $computedPoints = [];
        $piSlice = pi() / ($totalSlices - 1);
        $angle = $piSlice * $sliceIteration;

        foreach($votes as $voteIterator => $vote) {
            $point = new Point();
            $point->setX(
                (int)(($circleCentroidPoint->getX() + $radius) * cos($angle))
            );
            $point->setY(
                (int)(($circleCentroidPoint->getY() + $radius) * sin($angle))
            );
            $point->setAngle($angle);

            if ('D' === data_get($vote, 'party')) {
                if ('Nay' === $vote->vote) {
                    $point->color = [230, 248, 255];
                } else if ('Yea' === $vote->vote) {
                    $point->color = [0, 174, 243];
                    $point->isFilled = true;
                } else if ('Not Voting' === $vote->vote || 'Not Present' === $vote->vote) {
                    $point->color = [230, 248, 255];
                } else {
                    throw new Exception('Unable to read vote for vote ID: ' . $vote->id);
                }
            } else if ('R' === data_get($vote, 'party')) {
                if ('Nay' === $vote->vote) {
                    $point->color = [251, 234, 235];
                } else if ('Yea' === $vote->vote) {
                    $point->color = [232, 27, 35];
                    $point->isFilled = true;
                } else if ('Not Voting' === $vote->vote || 'Not Present' === $vote->vote) {
                    $point->color = [251, 234, 235];
                } else {
                    throw new Exception('Unable to read vote for vote ID: ' . $vote->id);
                }
            } else if ('I' === data_get($vote, 'party')) {
                if ('Nay' === $vote->vote) {
                    $point->color = [245, 223, 146];
                } else if ('Yea' === $vote->vote) {
                    $point->color = [241, 187, 0];
                    $point->isFilled = true;
                } else if ('Not Voting' === $vote->vote || 'Not Present' === $vote->vote) {
                    $point->color = [245, 223, 146];
                } else {
                    throw new Exception('Unable to read vote for vote ID: ' . $vote->id);
                }
            }

            array_push($computedPoints, $point);

            $radius += $point->getDiameter() * 2 * 2;
        }
        $computedPoints = collect($computedPoints)->reverse();

        return $computedPoints;
    }

    /**
     * Create House Graphic
     *
     * Creates a graphic for a House vote (named as such because this was taken from code that also generated Senate images)
     *
     * Pass in votes, if it passed or not, and a requirement, then enjoy your fresh chart graphic.
     *
     * @param bool $votePassed True if the vote passed
     * @param Collection $votes A collection fo votes directly from the JSON file we supplied
     * @param int $votePassRequirement The number of votes needed to pass
     * @return $this
     * @throws Exception
     */
    public function createHouseGraphic(bool $votePassed, Collection $votes, $votePassRequirement)
    {
        // Get Yea votes for drawing progress
        $yeaVotes = $votes->where('vote', 'Yea');

        // Create canvas
        $graphic = new VoteGraphic([
            'width'  => 270 * 2,
            'height' => 250 * 2
        ]);

        // Step 1: create vote pass/fail text
        $titleText = (true === $votePassed) ? 'VOTE PASSED' : 'VOTE FAILED';
        $graphic->drawTitle($titleText);

        // Step 2: draw progress bar representing percent of votes that are yea
        $progressFillColor = (true === $votePassed) ? imagecolorallocate($graphic->canvas, 232, 251, 237) : imagecolorallocate($graphic->canvas, 251, 232, 237);
        $progressFillPercent = ($yeaVotes->count() / $votes->count()) * $graphic->width;
        $graphic->drawProgressBar(
            $progressFillColor,
            $progressFillPercent,
            $votePassRequirement / $votes->count()
        );

        // Step 3: draw vote count sublabel
        $voteCountText = sprintf(
            '%s - %s',
            (int)$yeaVotes->count(),
            ((int)$votes->count() - (int)$yeaVotes->count())
        );
        $graphic->drawVoteCountLabel($voteCountText);

        // Step 4: draw each point on a half-circle, iterating through each arc
        $centerPoint = (new Point)->setX(80)->setY(80);
        $startingArcRadius = 70;
        for ($sliceIterator = 0; $sliceIterator < 44; $sliceIterator++) {
            $points = $votes->splice(0, 10);
            $arcPoints = $this->calculatePointsOnArcForSlice(
                $columns = 44,
                $columnIteration = $sliceIterator,
                $points,
                $startingArcRadius,
                $centerPoint
            );
            $graphic->drawArcSliceOnCanvas(
                $arcPoints,
                $xPadding = $graphic->width / 2.05,
                $yPadding = 110
            );
        }

        $this->graphic = $graphic;

        return $this;
    }

    /**
     * Get Exportable Graphic
     *
     * A simple helper method to get the `canvas` property of the VoteGraphic
     *
     * @return resource The gd resource
     */
    public function getExportableGraphic()
    {
        return data_get($this->graphic, 'canvas');
    }
}
