<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use DateInterval;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wescape\CoreBundle\Entity\User;

class PasswordResetUsersTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const TEST_EXPIRED_EMAIL = "expire@wescape.it";
    const TEST_VALID_EMAIL = "valid@wescape.it";
    const TEST_VALID_TOKEN = "AAAAAA";

    /** @var ContainerInterface */
    private $container;

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get("fos_user.user_manager");
        /** @var User $expired */
        $expired = $userManager->createUser()
            ->setUsername(self::TEST_EXPIRED_EMAIL)
            ->setEmail(self::TEST_EXPIRED_EMAIL)
            ->setPlainPassword("password")
            ->setRoles(['ROLE_USER'])
            ->setEnabled(true);
        $expirationDate = new \DateTime("now");
        $expirationDate->sub(new DateInterval('PT1H'));
        $expired->setResetPasswordToken(self::TEST_VALID_TOKEN)
            ->setResetTokenExpiresAt($expirationDate);

        /** @var User $valid */
        $valid = $userManager->createUser()
            ->setUsername(self::TEST_VALID_EMAIL)
            ->setEmail(self::TEST_VALID_EMAIL)
            ->setPlainPassword("password")
            ->setRoles(['ROLE_USER'])
            ->setEnabled(true);
        $expirationDate = new \DateTime("now");
        $expirationDate->add(new DateInterval('PT1H'));
        $valid->setResetPasswordToken(self::TEST_VALID_TOKEN)
            ->setResetTokenExpiresAt($expirationDate);

        $userManager->updateUser($expired);
        $userManager->updateUser($valid);
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getOrder() {
        return 1;
    }
}