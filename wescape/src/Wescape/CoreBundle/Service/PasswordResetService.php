<?php

namespace Wescape\CoreBundle\Service;


use FOS\UserBundle\Model\UserManager;
use Wescape\CoreBundle\Entity\User;

class PasswordResetService
{
    /** @var UserManager */
    private $userManager;
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var \Twig_Environment */
    private $twig;

    public function __construct(UserManager $userManager,
                                \Swift_Mailer $mailer,
                                \Twig_Environment $twig) {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Genera il secret code e lo invia all'utente per email
     *
     * @param User $user
     */
    public function request(User $user) {
        $secret = $this->getAlphaNumRandom(6);
        $user->setResetPasswordToken($secret)
            ->setResetRequestedAt(new \DateTime("now"));
        $this->userManager->updateUser($user);

        // @TODO Creare una mail degna di questo nome
        $messageBody = $this->twig->render("CoreBundle:Emails:password_reset.html.twig",
            array(
                "secret" => $secret
            ));

        // Inviare il codice per email
        $message = \Swift_Message::newInstance()
            ->setSubject("Password Reset")
            ->setFrom("wescape@gmail.com")
            ->setTo($user->getEmail())
            ->setBody($messageBody, "text/html");

        $this->mailer->send($message);
    }
    
    public function reset() {
        
    }

    /**
     * Genera una stringa casuale di lunghezza $length
     *
     * @param $length
     *
     * @return mixed
     */
    private function getAlphaNumRandom($length) {
        $randomNum = random_int(0, 36 ** $length - 1);
        return strtoupper(base_convert($randomNum, 10, 36));
    }
}