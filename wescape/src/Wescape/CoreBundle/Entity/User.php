<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupableInterface;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class User
 * Questa classe è una versione modificata del model User fornito dal FOSUserBundle.
 *
 * @see \FOS\UserBundle\Model\User
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserInterface, GroupableInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $passwordRequestedAt;

    /**
     * Not to persiste
     *
     * @var Collection
     */
    protected $groups;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $locked;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $expired;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $roles;

    public function __construct() {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->roles = array();
    }

    public function getUsernameCanonical() {
        return $this->getEmail();
    }

    public function getEmailCanonical() {
        return $this->getEmail();
    }

    public function getUsername() {
        return $this->getEmail();
    }

    public function addRole($role) {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }


    /**
     * Gets the groups granted to the user.
     *
     * @return Collection
     */
    public function getGroups() {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames() {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasGroup($name) {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(GroupInterface $group) {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(GroupInterface $group) {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Serializes the user.
     * The serialized data have to contain the fields used during check for
     * changes and the id.
     *
     * @return string
     */
    public function serialize() {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->expired,
            $this->locked,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->email,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized) {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->expired,
            $this->locked,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->email,
            ) = $data;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials() {
        $this->plainPassword = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getId() {
        return $this->id;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getEmail() {
        return $this->email;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }

    public function getConfirmationToken() {
        return $this->confirmationToken;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles() {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Never use this to check if this user has access to anything!
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role) {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isAccountNonExpired() {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isAccountNonLocked() {
        return !$this->locked;
    }

    public function isCredentialsNonExpired() {
        return false;
    }

    public function isCredentialsExpired() {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function isExpired() {
        return !$this->isAccountNonExpired();
    }

    public function isLocked() {
        return !$this->isAccountNonLocked();
    }

    public function isSuperAdmin() {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole($role) {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setUsername($username) {
        $this->email = $username;

        return $this;
    }

    public function setUsernameCanonical($usernameCanonical) {
        $this->email = $usernameCanonical;

        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    public function setEmailCanonical($emailCanonical) {
        $this->email = $emailCanonical;

        return $this;
    }

    public function setEnabled($boolean) {
        $this->enabled = (Boolean)$boolean;

        return $this;
    }

    /**
     * Sets this user to expired.
     *
     * @param Boolean $boolean
     *
     * @return User
     */
    public function setExpired($boolean) {
        $this->expired = (Boolean)$boolean;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setExpiresAt(\DateTime $date = null) {
        $this->expiresAt = $date;

        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    public function setSuperAdmin($boolean) {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    public function setPlainPassword($password) {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(\DateTime $time = null) {
        $this->lastLogin = $time;

        return $this;
    }

    public function setLocked($boolean) {
        $this->locked = $boolean;

        return $this;
    }

    public function setConfirmationToken($confirmationToken) {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null) {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt() {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl) {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
        $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles) {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function __toString() {
        return (string)$this->getEmail();
    }
}