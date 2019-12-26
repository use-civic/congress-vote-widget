<?php

namespace VoteResultWidget;

use Exception;
use Illuminate\Support\Collection;

class VoteParser
{
    /**
     * Data
     *
     * A Collection representation of the imported JSON data file.
     *
     * @var Collection
     */
    public $data;

    /**
     * Votes
     *
     * A representation of votes in the JSON data file. We eventually sort these and add data for labeling.
     *
     * @var Collection
     */
    public $votes;

    /**
     * Vote Passed
     *
     * True if the vote passed, false if not.
     *
     * @var bool
     */
    public $votePassed;

    /**
     * Chamber
     *
     * A single character `h` or `s` representing the House or Senate
     *
     * @var string h|s
     */
    public $chamber;

    /**
     * Parse From File Path
     *
     * Given a full file path to a valid congress.gov vote file, we parse the file and save metadata associated
     * with it.
     *
     * @return $this
     */
    public function parseFromFilePath($filePath)
    {
        $this->data = collect(@json_decode(file_get_contents($filePath)));
        $this->votes = collect(data_get($this->data, 'votes'));
        $this->votePassed = $this->getPassResult();
        $this->chamber = data_get($this->data, 'chamber');

        return $this;
    }

    /**
     * Get Pass Result
     *
     * Parses congress.gov data to determine if the vote passed or failed.
     *
     * You'd think this would be simple, but it isn't. We must account for motion actions (which have different labels)
     * in addition to the typical Passed/Failed labels.
     *
     * @return bool
     * @throws Exception When we encounter a vote that doesn't have a label we coded for.
     */
    public function getPassResult()
    {
        $voteResult = data_get($this->data, 'result');
        $votePassed = null;

        if ('Bill Passed' === $voteResult
            || 'Motion Agreed to' === $voteResult
            || 'Motion to Proceed Agreed to' === $voteResult
            || 'pass' === $voteResult
            || 'Passed' === $voteResult) {
            return true;
        } else if ('fail' === $voteResult
            || 'Motion Rejected' === $voteResult) {
            return false;
        }

        throw new Exception('Unable to get vote result property for vote: ' . data_get($this->data, 'vote_id'));
    }

    public function sortVotes()
    {
        $votes = $this->votes;

        // Group votes by party
        $flatVotesCollection = $this->votes->flatten();
        $partyGroupedVotes = $flatVotesCollection->groupBy(['party'])
            // Party with most total votes comes first
            ->sortByDesc(function(Collection $partyVote) {
                return $partyVote->count();
            });

        // Next: get 'yea' votes first
        $yeaVotes = collect($votes->get('Yea'))
            ->transform(function($vote) {
                $vote->vote = 'Yea';

                return $vote;
            });

        $nayVotes = collect($votes->get('Nay'))
            ->transform(function($vote) {
                $vote->vote = 'Nay';

                return $vote;
            });

        $noVote = collect($votes->get('Present'))
            ->concat(collect($votes->get('Not Voting')))
            ->filter()
            ->transform(function($vote) {
                $vote->vote = 'Not Voting';

                return $vote;
            });

        $partyVoteSort = collect($partyGroupedVotes->keys());
        $partyVoteSort->transform(function($partyKey) use ($yeaVotes, $nayVotes, $noVote) {
            $partyYea = $yeaVotes->where('party', $partyKey);
            $partyNo  = $noVote->where('party', $partyKey);
            $partyNay = $nayVotes->where('party', $partyKey);

            $outVotes = $partyYea->splice(
                0, $partyYea->count() / 1.5
            );
            $shuffledVotes = $partyYea->concat($partyNo)->concat($partyNay)->shuffle();

            return $outVotes->concat($shuffledVotes);
        });

        $this->votes = $partyVoteSort->flatten();

        return $this;
    }
}
