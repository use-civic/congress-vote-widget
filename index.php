<?php

require_once __DIR__ . '/vendor/autoload.php';

use VoteResultWidget\VoteParser;
use VoteResultWidget\GraphicGenerator;

/**
 * Step 1: Parse votes from the congress.gov JSON file
 */
$voteFilePath = __DIR__ . '/data/115th-congress-h3-2017.json';
$voteParser = (new VoteParser())->parseFromFilePath($voteFilePath);

// If you want to play around with vote sorting, you can skip the call below and do your own sort
$voteParser->sortVotes();

/**
 * Step 2: Create a graphic from the votes
 *
 * For demo purposes we are only doing a House vote to keep things simple & easily readable.
 */
$graphicGenerator = (new GraphicGenerator())->createHouseGraphic(
    $voteParser->getPassResult(),
    $voteParser->votes,
    $voteParser->getVotesRequirement()
);


/**
 * Step 3: Save to a PNG in the export folder
 */
$outputTo = sprintf(
    '%s/%s.png',
    __DIR__ . '/export',
    data_get($voteParser->data, 'vote_id')
);
imagepng(
    $graphicGenerator->getExportableGraphic(),
    $outputTo
);

