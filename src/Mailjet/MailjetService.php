<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Event\MailjetEvent;
use AppBundle\Mailjet\Event\MailjetEvents;
use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\Message\MailjetMessage;
use AppBundle\Mailjet\Transport\TransportInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailjetService
{
    const PAYLOAD_MAXSIZE = 50;

    private $dispatcher;
    private $transport;
    private $factory;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        TransportInterface $transport,
        EmailTemplateFactory $factory
    ) {
        $this->dispatcher = $dispatcher;
        $this->transport = $transport;
        $this->factory = $factory;
    }

    public function sendMessage(MailjetMessage $message): bool
    {
        $delivered = true;
        $email = $this->factory->createFromMailjetMessage($message);

        try {
            $this->dispatcher->dispatch(MailjetEvents::DELIVERY_MESSAGE, new MailjetEvent($message, $email));
            $this->transport->sendTemplateEmail($email);
            $this->dispatcher->dispatch(MailjetEvents::DELIVERY_SUCCESS, new MailjetEvent($message, $email));
        } catch (MailjetException $exception) {
            $delivered = false;
            $this->dispatcher->dispatch(MailjetEvents::DELIVERY_ERROR, new MailjetEvent($message, $email, $exception));
        }

        return $delivered;
    }
}
