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

namespace App\Tests\Entity;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Status;
use App\Entity\Document;
use PHPUnit\Framework\TestCase;

/**
 * The class for the testing the Status entity.
 *
 * @category Class
 *
 * @license  GNU General Public License version 2 or later
 */
class DocumentTest extends TestCase
{
    /**
     * Testing the identification number (id).
     *
     * @throws Exception
     */
    public function testId(): void
    {
        $document = new Document();
        $document->setId(1);

        $this->assertEquals(1, $document->getId());
        $this->assertEquals('1', $document->getId());
        $this->assertEquals('string', gettype($document->getId()));
        $this->assertNotEquals('integer', $document->getId());
        $this->assertNotEquals('object', $document->getId());
        $this->assertNotEquals(0, $document->getId());
        $this->assertNotEquals('', $document->getId());
        $this->assertNotEquals([], $document->getId());
    }

    /**
     * Testing the payload.
     *
     * @throws Exception
     */
    public function testPayload(): void
    {
        $payload = (object) json_encode(['draft']);

        $document = new Document();
        $document->setPayload((object) json_encode(['draft']));

        $this->assertEquals($payload, $document->getPayload());
        $this->assertEquals('object', gettype($document->getPayload()));

        $this->assertNotEquals('integer', gettype($document->getPayload()));
        $this->assertNotEquals('array', gettype($document->getPayload()));
        $this->assertNotEquals('string', gettype($document->getPayload()));
        $this->assertNotEquals(0, $document->getPayload());
        $this->assertNotEquals('', $document->getPayload());
        $this->assertNotEquals('DRAFT', $document->getPayload());
        $this->assertNotEquals([], $document->getPayload());
    }

    /**
     * Testing the string representation of the object.
     *
     * @throws Exception
     */
    public function testToString(): void
    {
        $document = new Document();
        $document->setId('11-11');

        $this->assertEquals('11-11', $document);
        $this->assertEquals('object', gettype($document));
        $this->assertEquals('string', gettype($document->__toString()));
        $this->assertNotEquals(0, $document);
        $this->assertNotEquals('0', $document);
        $this->assertNotEquals('', $document);
        $this->assertNotEquals('1111', $document);
        $this->assertNotEquals([], $document);
    }

    /**
     * Testing the status.
     *
     * @throws Exception
     */
    public function testStatus(): void
    {
        $status = new Status();
        $status->setId(1);
        $status->setTitle('draft');

        $document = new Document();
        $document->setStatus($status);

        $this->assertEquals('object', gettype($document->getStatus()));
        $this->assertEquals('string', gettype($document->getStatus()->getTitle()));
        $this->assertEquals('draft', $document->getStatus()->getTitle());
        $this->assertNotEquals('array', gettype($document->getStatus()->getTitle()));
        $this->assertNotEquals('', $document->getStatus()->getTitle());
    }

    /**
     * Testing the "created at".
     *
     * @throws Exception
     */
    public function testCreatedAt(): void
    {
        $time = new DateTime();

        $document = new Document();
        $document->setCreatedAt($time);

        $this->assertEquals($time, $document->getCreatedAt());
        $this->assertEquals('object', gettype($document->getCreatedAt()));
        $this->assertNotEquals('string', gettype($document->getCreatedAt()));
        $this->assertNotEquals('array', gettype($document->getCreatedAt()));
    }

    /**
     * Testing the "modified at".
     *
     * @throws Exception
     */
    public function testModifiedAt(): void
    {
        $time = new DateTime();

        $document = new Document();
        $document->setModifiedAt($time);

        $this->assertEquals($time, $document->getModifiedAt());
        $this->assertEquals('object', gettype($document->getModifiedAt()));
        $this->assertNotEquals('string', gettype($document->getModifiedAt()));
        $this->assertNotEquals('array', gettype($document->getModifiedAt()));
    }

    /**
     * Testing the user.
     *
     * @throws Exception
     */
    public function testUser(): void
    {
        $user = new User();

        $document = new Document();
        $document->setUser($user);

        $this->assertEquals($user, $document->getUser());
        $this->assertEquals('object', gettype($document->getUser()));
        $this->assertNotEquals('string', gettype($document->getUser()));
        $this->assertNotEquals('array', gettype($document->getUser()));
    }
}
