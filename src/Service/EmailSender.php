<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\User;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;

class EmailSender
{
    use ControllerTrait;
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $emailFrom;

    public function __construct(string $emailFrom, Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->emailFrom = $emailFrom;
        $this->container = $container;
    }

    public function sendEmail(User $user, User $recipient, Activity $activity, $subject): void
    {
        $message = (new Swift_Message(
            $user->getName() . ' ' . $user->getSurname() . $subject . $activity->getName()
        ))
            ->setFrom($this->emailFrom)
            ->setTo($recipient->getEmail())
            ->setBody(
                $this->renderView(
                    'mail/mail.html.twig',
                    [
                        'user' => $user,
                        'subject' => $subject,
                        'recipient' => $recipient,
                        'activity' => $activity
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
