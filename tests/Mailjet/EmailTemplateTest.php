<?php

namespace Tests\AppBundle\Mailjet;

use AppBundle\Mailjet\EmailTemplate;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\Test\Mailjet\Message\DummyMessage;

class EmailTemplateTest extends TestCase
{
    /**
     * @expectedException \AppBundle\Mailjet\Exception\MailjetException
     * @expectedExceptionMessage The Mailjet email requires at least one recipient.
     */
    public function testCreateMailjetTemplateEmailWithoutRecipients()
    {
        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr');
        $email->getBody();
    }

    public function testCreateMailjetTemplateEmail()
    {
        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);
        $email->addRecipient('vincent777h@example.tld', 'Vincent Durand', ['name' => 'Vincent Durand']);

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'FromName' => 'En Marche !',
            'Subject' => 'Votre donation !',
            'MJ-TemplateID' => '12345',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'john.smith@example.tld',
                    'Name' => 'John Smith',
                    'Vars' => [
                        'name' => 'John Smith',
                    ],
                ],
                [
                    'Email' => 'vincent777h@example.tld',
                    'Name' => 'Vincent Durand',
                    'Vars' => [
                        'name' => 'Vincent Durand',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateMailjetTemplateEmailWithReplyTo()
    {
        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !', 'reply@to.me');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'FromName' => 'En Marche !',
            'Subject' => 'Votre donation !',
            'MJ-TemplateID' => '12345',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'john.smith@example.tld',
                    'Name' => 'John Smith',
                    'Vars' => [
                        'name' => 'John Smith',
                    ],
                ],
            ],
            'Headers' => [
                'Reply-To' => 'reply@to.me',
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateMailjetTemplateEmailFromDummyMessage()
    {
        $email = EmailTemplate::createWithMailjetMessage(DummyMessage::create(), 'contact@en-marche.fr');

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'Subject' => 'Dummy Message',
            'MJ-TemplateID' => '66666',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'dummy@example.tld',
                    'Name' => 'Dummy User',
                    'Vars' => [
                        'dummy' => 'ymmud',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }
}
