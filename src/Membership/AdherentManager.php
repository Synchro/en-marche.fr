<?php

namespace AppBundle\Membership;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\BaseEvent;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;

class AdherentManager
{
    private $manager;
    private $repository;

    public function __construct(
        AdherentRepository $repository,
        ObjectManager $manager
    ) {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    public function activateAccount(Adherent $adherent, AdherentActivationToken $token, bool $flush = true)
    {
        $adherent->activate($token);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function countActiveAdherents(): int
    {
        return $this->repository->countActiveAdherents();
    }

    public function findByEvent(BaseEvent $event): AdherentCollection
    {
        return $this->repository->findByEvent($event);
    }

    public function findNearByCitizenInitiativeInterests(CitizenInitiative $citizenInitiative): AdherentCollection
    {
        return $this->repository->findNearByCitizenInitiativeInterests($citizenInitiative);
    }

    public function findSupervisorsNearCitizenInitiative(CitizenInitiative $citizenInitiative): AdherentCollection
    {
        return $this->repository->findSupervisorsNearCitizenInitiative($citizenInitiative);
    }

    public function findSubscribersToAdherentActivity(Adherent $followedAdherent): AdherentCollection
    {
        return $this->repository->findSubscribersToAdherentActivity($followedAdherent);
    }
}
