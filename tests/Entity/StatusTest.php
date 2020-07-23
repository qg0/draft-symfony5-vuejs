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

use Exception;
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
class StatusTest extends TestCase
{
    /**
     * Testing the identification number (id).
     *
     * @throws Exception
     */
    public function testId(): void
    {
        $status = new Status();
        $status->setId('11-11');
        $this->assertEquals('11-11', $status->getId());
        $this->assertEquals('string', gettype($status->getId()));
        $this->assertNotEquals('integer', $status->getId());
        $this->assertNotEquals('object', $status->getId());
        $this->assertNotEquals(0, $status->getId());
        $this->assertNotEquals('0', $status->getId());
        $this->assertNotEquals('', $status->getId());
        $this->assertNotEquals('1111', $status->getId());
        $this->assertNotEquals([], $status->getId());
    }

    /**
     * Testing the title.
     *
     * @throws Exception
     */
    public function testTitle(): void
    {
        $status = new Status();
        $status->setTitle('draft');
        $this->assertEquals('draft', $status->getTitle());
        $this->assertEquals('draft'.'', $status->getTitle());
        $this->assertEquals('string', gettype($status->getTitle()));
        $this->assertNotEquals('integer', gettype($status->getTitle()));
        $this->assertNotEquals('object', gettype($status->getTitle()));
        $this->assertNotEquals(0, $status->getTitle());
        $this->assertNotEquals('', $status->getTitle());
        $this->assertNotEquals('DRAFT', $status->getTitle());
        $this->assertNotEquals([], $status->getTitle());
    }

    /**
     * Testing the string representation of the object.
     *
     * @throws Exception
     */
    public function testToString(): void
    {
        $status = new Status();
        $status->setTitle('draft');
        $this->assertEquals('draft', $status);
        $this->assertEquals('draft'.'', $status);
        $this->assertEquals('object', gettype($status));
        $this->assertEquals('string', gettype($status->__toString()));
        $this->assertNotEquals('integer', gettype($status));
        $this->assertNotEquals('string', gettype($status));
        $this->assertNotEquals(0, $status);
        $this->assertNotEquals('', $status);
        $this->assertNotEquals('DRAFT', $status);
        $this->assertNotEquals([], $status);
    }

    /**
     * Testing the related documents.
     *
     * @throws Exception
     */
    public function testDocuments(): void
    {
        $status   = new Status();
        $document = new Document();
        $document->setId(1);
        $status->setDocuments([$document]);
        $this->assertEquals('array', gettype($status->getDocuments()));
        $this->assertNotEquals('object', gettype($status->getDocuments()));
        $this->assertEquals(1, $status->getDocuments()[0]->getId());
        $this->assertEquals('string', gettype($status->getDocuments()[0]->getId()));
        $this->assertNotEquals([], $status->getDocuments());
    }
}
