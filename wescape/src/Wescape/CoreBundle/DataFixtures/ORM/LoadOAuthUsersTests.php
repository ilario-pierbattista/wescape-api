<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wescape\CoreBundle\Entity\User;

class LoadOAuthUsersTests extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const TEST_ADMIN_EMAIL = "admin";
    const TEST_ADMIN_PASS = "admin";
    const TEST_USER_EMAIL = "user";
    const TEST_USER_PASS = "user";
    
    /** @var ContainerInterface */
    private $container;

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get("fos_user.user_manager");
        /** @var User $admin */
        $admin = $userManager->createUser()
            ->setUsername(self::TEST_ADMIN_EMAIL)
            ->setEmail(self::TEST_ADMIN_EMAIL)
            ->setPlainPassword(self::TEST_ADMIN_PASS)
            ->setRoles(['ROLE_ADMIN'])
            ->setEnabled(true);
        
        /** @var User $user */
        $user1 = $userManager->createUser()
            ->setUsername(self::TEST_USER_EMAIL)
            ->setEmail(self::TEST_USER_EMAIL)
            ->setPlainPassword(self::TEST_USER_PASS)
            ->setRoles(['ROLE_USER'])
            ->setEnabled(true);
        
        /** @var User $user2 */
        $user2 = $userManager->createUser()
            ->setUsername("test2@wescape.it")
            ->setEmail("test2@wescape.it")
            ->setPlainPassword("test2")
            ->setRoles(['ROLE_USER'])
            ->setEnabled(true);

        $userManager->updateUser($admin);
        $userManager->updateUser($user1);
        $userManager->updateUser($user2);
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getOrder() {
        return 0;
    }
}