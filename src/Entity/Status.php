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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Table(name="status")
 * @ORM\Entity(repositoryClass="App\Repository\StatusRepository")
 */
class Status
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
     * The title.
     *
     * @ORM\Column(type="text")
     */
    private string $title;

    /**
     * Related documents.
     *
     * @var array|PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Document", mappedBy="status")
     */
    private $documents;

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
     * Get the title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title.
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get the string representation of the object.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * Get the related documents.
     *
     * @return array
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * Set the related documents.
     *
     * @param array|PersistentCollection $documents
     *
     * @return void
     */
    public function setDocuments($documents): void
    {
        $this->documents = $documents;
    }
}
