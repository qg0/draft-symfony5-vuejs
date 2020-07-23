<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

namespace App\Entity;

use DateTime;
use Exception;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private $id;

    /**
     * The login.
     *
     * @ORM\Column(type="string",  unique=true)
     */
    private $login;

    /**
     * The password.
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * User roles.
     *
     * @ORM\Column(type="simple_array")
     */
    private $roles = [];

    /**
     * Related documents.
     *
     * @ORM\OneToMany(targetEntity="Document", mappedBy="user")
     *
     * @var array|PersistentCollection
     */
    private $documents;

    /**
     * Token.
     *
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private string $token;

    /**
     * Token is valid until this time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private DateTime $until;

    /**
     * Get the identification number (id).
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the identification number (id).
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the salt.
     *
     * @return string
     */
    public function getSalt(): string
    {
        return '';
    }

    /**
     * Get the password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the user roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set user roles.
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Erase credentials.
     *
     * @return void
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Check if the user has a necessary role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles && (false !== array_search($role, $this->roles));
    }

    /**
     * Check if the user has the only necessary role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasOnlyRole($role): bool
    {
        return 1 === count($this->roles) && $this->hasRole($role);
    }

    /**
     * Serialize the object.
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->login,
            $this->password,
            $this->roles,
            $this->token,
            $this->until,
        ]);
    }

    /**
     * Unserialize the object.
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->login,
            $this->password,
            $this->roles,
            $this->token,
            $this->until,
            ] = unserialize($serialized);
    }

    /**
     * Get the string representation of the object.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->login;
    }

    /**
     * Get the login.
     *
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->login;
    }

    /**
     * Set the username.
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->login = $username;
    }

    /**
     * Set the login.
     *
     * @param string $login
     *
     * @return void
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * Get related documents.
     *
     * @return object
     */
    public function getDocuments(): object
    {
        return (object) $this->documents;
    }

    /**
     * Set related documents.
     *
     * @param array $documents
     *
     * @return void
     */
    public function setDocuments(array $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set the token.
     *
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Get the time when the token is valid.
     *
     * @throws Exception
     *
     * @return DateTime
     */
    public function getUntil(): DateTime
    {
        return $this->until;
    }

    /**
     * Set the time when the token is valid.
     *
     * @param DateTime $until
     *
     * @throws Exception
     *
     * @return void
     */
    public function setUntil(DateTime $until): void
    {
        $this->until = $until;
    }
}
