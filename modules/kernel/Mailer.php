<?php

namespace Quantum;

/**
 * Class Mailer
 * @package Quantum
 */
class Mailer {


    /**
     * Mailer constructor.
     */
    function __construct() {
   
    }

    /**
     * @param $subject
     * @param $destination_email
     * @param $destination_name
     * @param $html_contents
     * @param string $text_contents
     * @return bool|string
     * @throws \phpmailerException
     */
    static function sendEmail($subject, $destination_email, $destination_name, $html_contents, $text_contents = "")
    {
        if (empty($text_contents))
            $text_contents = $html_contents;

        $transport = (new \Swift_SmtpTransport('smtp.mailgun.org', 587))
            ->setUsername('notifications@ccstores.host')
            ->setPassword('dfJukSDPeqHdCXRu');

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message($subject))
            ->setFrom(['notifications@ccstores.host' => 'Quantum Mailer'])
            ->setReplyTo(['notifications@ccstores.host' => 'Quantum Mailer'])
            ->setTo([$destination_email => $destination_name])
            ->setBody($html_contents, 'text/html')
            ->addPart($text_contents, 'text/plain');

        $result = $mailer->send($message);

        return $result;
    }

    /**
     * @param $subject
     * @param $message
     */
    public static function notifyCreator($subject, $message)
    {
        $email = "cbarbosa@clubcolors.com";
        $name = "Admin";

        \TransactionalEmail::sendEmail($subject, $email, $name, $message);

    }
    
    
    
   
    
    
    
}