<?php


namespace Enhavo\Bundle\NewsletterBundle\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Enhavo\Bundle\NewsletterBundle\Entity\Newsletter;

/**
 * NewsletterManager.php
 *
 * @since 05/07/16
 * @author gseidel
 */
class NewsletterManager
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $config;

    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, $config)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function send(Newsletter $newsletter)
    {
        if($newsletter->getSent()) {
            return;
        }

        $subscriber = $this->em
            ->getRepository('EnhavoNewsletterBundle:Subscriber')
            ->findBy(array('active' => true));

        for($i = 0; $i < count($subscriber); $i++)
        {
            $message = \Swift_Message::newInstance()
                ->setSubject($newsletter->getSubject())
                ->setContentType("text/html")
                ->setFrom($this->config['send_from'])
                ->setTo($subscriber[$i]->getEmail())
                ->setBody($newsletter->getText());
            $this->get('mailer')->send($message);
        }

        $newsletter->setSent(true);
        $this->em->persist($newsletter);
        $this->em->flush();
    }
}