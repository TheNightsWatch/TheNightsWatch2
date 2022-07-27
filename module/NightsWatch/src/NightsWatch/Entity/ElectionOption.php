<?php

namespace NightsWatch\Entity;

/**
 * A candidate in an election
 *
 * @ORM\Entity
 * @ORM\Table(name="electionOption")
 */
class ElectionOption
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Election
     * @ORM\ManyToOne(targetEntity="Election", inversedBy="options")
     * @ORM\JoinColumn(name="electionId", referencedColumnName="id")
     */
    protected $election;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $title = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected $description = null;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user = null;

    /**
     * @var ElectionVote[]
     * @ORM\OneToMany(targetEntity="ElectionVote", mappedBy="option")
     */
    protected $votes;

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
