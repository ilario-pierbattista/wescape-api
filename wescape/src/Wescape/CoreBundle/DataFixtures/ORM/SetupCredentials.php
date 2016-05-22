<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wescape\CoreBundle\Entity\Client;
use Wescape\CoreBundle\Entity\User;

class SetupCredentials extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get("fos_user.user_manager");
        /** @var User $admin */
        $admin = $userManager->createUser()
            ->setUsername("admin")
            ->setEmail("wescapeadmin")
            ->setPlainPassword("admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setEnabled(true);
        $userManager->updateUser($admin);

        /** @var User $user */
        $user = $userManager->createUser()
            ->setUsername("wescape@mailinator.com")
            ->setEmail("wescape@mailinator.com")
            ->setPlainPassword("password")
            ->setEnabled(true);
        $userManager->updateUser($user);

        // Client per l'upload dei dati
        $loaderClient = new Client();
        $loaderClient->setRandomId($this->container
            ->getParameter("oauth2_clients.data_loader.id"));
        $loaderClient->setSecret($this->container
            ->getParameter("oauth2_clients.data_loader.secret"));
        $loaderClient->setAllowedGrantTypes(["password", "refresh_token"]);
        $loaderClient->setRedirectUris([]);

        // Client per l'applicazione android
        $appClient = new Client();
        $appClient->setRandomId($this->container
            ->getParameter("oauth2_clients.wescape_app.id"));
        $appClient->setSecret($this->container
            ->getParameter("oauth2_clients.wescape_app.secret"));
        $appClient->setAllowedGrantTypes(["password", "refresh_token"]);
        $appClient->setRedirectUris([]);

        $manager->persist($loaderClient);
        $manager->persist($appClient);
        $manager->flush();
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getOrder() {
        return 0;
    }
}