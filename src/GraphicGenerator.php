<?php

namespace VoteResultWidget;

use Exception;
use Illuminate\Support\Collection;

class GraphicGenerator
{
    public $graphic;

    /**
     * Calculate Points on Arc for Slice
     *
     * A very complex function that should be refactored and described
     * @todo refactor and describe
     *
     * @param $totalSlices
     * @param $sliceIteration
     * @param Collection $votes
     * @param $radius
     * @param Point $circleCentroidPoint
     * @return array|Collection|\Tightenco\Collect\Support\Collection
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
                } else if ('Not Voting' === $vote->vote || 'Not Present' === $vote->vote) {
                    $point->color = [245, 223, 146];
                } else {
                    throw new Exception('Unable to read vote for vote ID: ' . $vote->id);
                }
            }

            array_push($computedPoints, $point);

            $radius += $point->getDiameter() * 2;
        }
        $computedPoints = collect($computedPoints)->reverse();

        return $computedPoints;
    }

    public function createHouseGraphic(bool $votePassed, Collection $votes)
    {
        // Get Yea votes for drawing progress
        $yeaVotes = $votes->where('vote', 'Yea');

        // Create canvas
        $graphic = new VoteGraphic([
            'width'  => 270,
            'height' => 250
        ]);

        // Step 1: create vote pass/fail text
        $titleText = (true === $votePassed) ? 'VOTE PASSED' : 'VOTE FAILED';
        $graphic->drawTitle($titleText);

        // Step 2: draw progress bar representing percent of votes that are yea
        $progressFillColor = (true === $votePassed) ? imagecolorallocate($graphic->canvas, 232, 251, 237) : imagecolorallocate($graphic->canvas, 251, 232, 237);
        $progressFillPercent = ($yeaVotes->count() / $votes->count()) * $graphic->width;
        $graphic->drawProgressBar(
            $progressFillColor,
            $progressFillPercent
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
        $startingArcRadius = 0;
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

    public function getExportableGraphic()
    {
        return data_get($this->graphic, 'canvas');
    }
}
