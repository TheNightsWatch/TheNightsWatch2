<?php

namespace NightsWatch\Entity;

use DateTime;

/**
 * An Election
 *
 * @ORM\Entity
 * @ORM\Table(name="election")
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property DateTime $startDate
 * @property DateTime $endDate
 * @property int $voteType
 * @property ElectionOption[] $electionOptions
 * @property ElectionVote[] $electionVotes
 */
class Election
{
    const VOTE_TYPE_ACCEPTANCE = 1;
    const VOTE_TYPE_STV = 2;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $startDate;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $endDate;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $voteType;

    /**
     * @var ElectionOption[]
     * @ORM\OneToMany(targetEntity="ElectionOption", mappedBy="election")
     */
    protected $options;

    /**
     * @var ElectionVote[]
     * @ORM\OneToMany(targetEntity="ElectionVote", mappedBy="election")
     */
    protected $votes;

    /**
     * @var int
     */
    protected $lowestRankEligibleToVote = User::RANK_PRIVATE;

    public function __get($property)
    {
        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }

    public function populate(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
