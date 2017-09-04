<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\DonationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransactionCallbackHandler
{
    private $router;
    private $entityManager;
    private $mailjet;
    private $donationRequestUtils;

    public function __construct(UrlGeneratorInterface $router, ObjectManager $entityManager, MailjetService $mailjet, DonationRequestUtils $donationRequestUtils)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->mailjet = $mailjet;
        $this->donationRequestUtils = $donationRequestUtils;
    }

    public function handle(string $uuid, Request $request, string $callbackToken): Response
    {
        $donation = $this->entityManager->getRepository(Donation::class)->findOneByUuid($uuid);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        if (!$donation->isFinished()) {
            $donation->finish($this->donationRequestUtils->extractPayboxResultFromCallBack($request, $callbackToken));

            $this->entityManager->flush();

            $campaignExpired = (bool) $request->attributes->get('_campaign_expired', false);
            if (!$campaignExpired && $donation->isSuccessful()) {
                $this->mailjet->sendMessage(DonationMessage::createFromDonation($donation));
            }
        }

        return new RedirectResponse($this->router->generate(
            'donation_result',
            $this->donationRequestUtils->createCallbackStatus($donation)
        ));
    }
}
