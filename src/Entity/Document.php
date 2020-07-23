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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 */
class Document
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private string $id;

    /**
     * Related user.
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="documents", cascade={"remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private User $user;

    /**
     * Related status.
     *
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="documents")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="set null")
     */
    private Status $status;

    /**
     * The document payload.
     *
     * @ORM\Column(type="json_array")
     *
     * @var string
     */
    private $payload;

    /**
     * Creation time.
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime")
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * Modification time.
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime")
     *
     * @var DateTime
     */
    private DateTime $modifiedAt;

    /**
     * Get the identification number (id).
     *
     * @throws Exception
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
     * @throws Exception
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the string representation of the object.
     *
     * @throws Exception
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * Get the status.
     *
     * @throws Exception
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param Status $status
     *
     * @throws Exception
     *
     * @return void
     */
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the user.
     *
     * @throws Exception
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the user.
     *
     * @param User $user
     *
     * @throws Exception
     *
     * @return void
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the payload.
     *
     * @throws Exception
     *
     * @return object
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the payload.
     *
     * @param object $payload
     *
     * @throws Exception
     */
    public function setPayload(object $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Get the creation time.
     *
     * @throws Exception
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the creation time.
     *
     * @param DateTime $createdAt
     *
     * @throws Exception
     *
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the modification time.
     *
     * @throws Exception
     *
     * @return DateTime
     */
    public function getModifiedAt(): DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * Set the modification time.
     *
     * @param DateTime $modifiedAt
     *
     * @throws Exception
     *
     * @return void
     */
    public function setModifiedAt(DateTime $modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }
}
