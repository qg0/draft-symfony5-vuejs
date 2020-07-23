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

namespace App\Adapter;

use DateTimeZone;
use App\Entity\Document;
use Exception as ExceptionAlias;

/**
 * The DocumentAdapter class with a purpose to convert the Document object to the proper response view.
 *
 * @category Class
 *
 * @license  GNU General Public License version 2 or later
 */
class DocumentAdapter
{
    /**
     * The unique auto incremented primary key.
     *
     * @var string
     */
    public string $id;

    /**
     * Related status.
     *
     * @var string
     */
    public string $status;

    /**
     * Payload.
     *
     * @var object|array
     */
    public $payload;

    /**
     * Creation time.
     *
     * @var string
     */
    public string $createAt;

    /**
     * Modification time.
     *
     * @var string
     */
    public string $modifyAt;

    /**
     * DocumentAdapter constructor.
     *
     * @param Document $document
     *
     * @throws ExceptionAlias
     */
    public function __construct(Document $document)
    {
        $timeZone = new DateTimeZone($_ENV['TIME_ZONE']);

        $this->setId($document->getId());
        $this->setStatus($document->getStatus()->getTitle());
        $this->setPayload($document->getPayload() ? $document->getPayload() : (object) []);
        $this->setCreateAt($document->getCreatedAt()->setTimezone($timeZone)->format(DATE_ISO8601));
        $this->setModifyAt($document->getModifiedAt()->setTimezone($timeZone)->format(DATE_ISO8601));
    }

    /**
     * Get the identification number.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the identification number.
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
     * Get the status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the payload.
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Set the payload.
     *
     * @param object|array $payload
     *
     * @return void
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Get the creation time.
     *
     * @return string
     */
    public function getCreateAt(): string
    {
        return $this->createAt;
    }

    /**
     * Set the creation time.
     *
     * @param string $createAt
     *
     * @return void
     */
    public function setCreateAt(string $createAt): void
    {
        $this->createAt = $createAt;
    }

    /**
     * Get the modification time.
     *
     * @return string
     */
    public function getModifyAt(): string
    {
        return $this->modifyAt;
    }

    /**
     * Set the modification time.
     *
     * @param string $modifyAt
     *
     * @return void
     */
    public function setModifyAt(string $modifyAt): void
    {
        $this->modifyAt = $modifyAt;
    }
}
