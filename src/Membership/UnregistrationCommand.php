<?php

namespace AppBundle\Membership;

use Symfony\Component\Validator\Constraints as Assert;

class UnregistrationCommand
{
    /**
     * @Assert\NotBlank(message="adherent.unregistration.reasons")
     */
    private $reasons = [];

    /**
     * @Assert\Length(min=10, max=1000)
     */
    private $comment;

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getReasonsAsJson(): string
    {
        return \GuzzleHttp\json_encode($this->reasons, JSON_PRETTY_PRINT);
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
