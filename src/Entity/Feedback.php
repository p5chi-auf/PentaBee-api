<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 */
class Feedback
{
    /**
     * Feedback ID
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Rating in stars (int(1-5))
     * @ORM\Column(type="integer")
     * @Groups({"AddFeedback"})
     */
    private $stars;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"AddFeedback"})
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn()
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn()
     * @Groups({"AddFeedback"})
     */
    private $userTo;

    /**
     * @ORM\ManyToOne(targetEntity="Activity")
     * @ORM\JoinColumn()
     * @Groups({"AddFeedback"})
     */
    private $activity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): void
    {
        $this->stars = $stars;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getUserFrom(): ?User
    {
        return $this->userFrom;
    }

    public function setUserFrom(?User $userFrom): void
    {
        $this->userFrom = $userFrom;
    }

    public function getUserTo(): ?User
    {
        return $this->userTo;
    }

    public function setUserTo(?User $userTo): void
    {
        $this->userTo = $userTo;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): void
    {
        $this->activity = $activity;
    }
}
