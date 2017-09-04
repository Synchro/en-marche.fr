<?php

namespace AppBundle\Search;

use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;

class SearchResultsProvider
{
    private $committeeRepository;
    private $eventRepository;

    public function __construct(
        CommitteeRepository $committeeRepository,
        EventRepository $eventRepository
    ) {
        $this->committeeRepository = $committeeRepository;
        $this->eventRepository = $eventRepository;
    }

    public function find(SearchParametersFilter $search): array
    {
        if (SearchParametersFilter::TYPE_COMMITTEES === $search->getType()) {
            return $this->committeeRepository->searchCommittees($search);
        }

        if (SearchParametersFilter::TYPE_EVENTS === $search->getType()) {
            return $this->eventRepository->searchAllEvents($search);
        }

        throw new \RuntimeException(sprintf(
            'This provider is not able to handle the search type "%s"',
            $search->getType()
        ));
    }
}
