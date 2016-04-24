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
    /** @var ContainerInterface */
    private $container;

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get("fos_user.user_manager");
        /** @var User $admin */
        $admin = $userManager->createUser()
            ->setUsername("admin")
            ->setEmail("admin")
            ->setPlainPassword("admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setEnabled(true);
        
        /** @var User $user */
        $user = $userManager->createUser()
            ->setUsername("user")
            ->setEmail("user")
            ->setPlainPassword("user")
            ->setRoles(['ROLE_USER'])
            ->setEnabled(true);

        $userManager->updateUser($admin);
        $userManager->updateUser($user);
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getOrder() {
        return 0;
    }
}