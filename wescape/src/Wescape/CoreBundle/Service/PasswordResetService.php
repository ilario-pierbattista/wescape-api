<?php

namespace Wescape\CoreBundle\Service;


use DateInterval;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Validator\Constraints\DateTime;
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
        $expirationDate = new \DateTime("now");
        $expirationDate->add(new DateInterval('PT1H'));

        $user->setResetPasswordToken($secret)
            ->setResetTokenExpiresAt($expirationDate);
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

    /**
     * Reimposta la password dell'utente
     *
     * @param User   $user        Utente di cui reimpostare la password
     * @param string $resetToken  Token segreto per il reset
     * @param string $newPassword Nuova password
     *
     * @return User
     * @throws \Exception
     */
    public function reset(User $user, $resetToken, $newPassword) {
        $now = new \DateTime("now");
        if ($resetToken != $user->getResetPasswordToken()) {
            throw new \Exception("The secret token is invalid");
        }
        if ($now >= $user->getResetTokenExpiresAt()) {
            throw new \Exception("The secret token has expired");
        }
        // A questo punto il token non è scaduto ed è ancora valido
        $user->setPlainPassword($newPassword)
            ->setResetPasswordToken(null)
            ->setResetTokenExpiresAt(null);
        $this->userManager->updateUser($user);

        return $user;
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