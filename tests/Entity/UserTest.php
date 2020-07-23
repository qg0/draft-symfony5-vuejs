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
use App\Entity\Document;
use PHPUnit\Framework\TestCase;

/**
 * The class for the testing the User entity.
 *
 * @category Class
 *
 * @license  GNU General Public License version 2 or later
 */
class UserTest extends TestCase
{
    /**
     * Testing the identification number (id).
     */
    public function testId(): void
    {
        $user = new User();
        $user->setId(1);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('1', $user->getId());
        $this->assertEquals('string', gettype($user->getId()));
        $this->assertNotEquals('integer', $user->getId());
        $this->assertNotEquals('object', $user->getId());
        $this->assertNotEquals(0, $user->getId());
        $this->assertNotEquals('', $user->getId());
        $this->assertNotEquals([], $user->getId());
    }

    /**
     * Testing the password salt.
     */
    public function testSalt(): void
    {
        $user = new User();

        $this->assertEquals('', $user->getSalt());
        $this->assertEquals('string', gettype($user->getSalt()));
        $this->assertNotEquals('NULL', gettype($user->getSalt()));
        $this->assertNotEquals('array', gettype($user->getSalt()));
        $this->assertNotEquals(0, $user->getSalt());
        $this->assertNotEquals('object', gettype($user->getSalt()));
        $this->assertNotEquals([], $user->getSalt());
        $this->assertEquals(false, $user->getSalt());
    }

    /**
     * Testing the password.
     */
    public function testPassword(): void
    {
        $user    = new User();
        $userSet = $user->setPassword('0a');

        $this->assertEquals($userSet, $user);
        $this->assertEquals('object', gettype($userSet));
        $this->assertEquals('string', gettype($userSet->getPassword()));
        $this->assertEquals('0a', $userSet->getPassword());
        $this->assertNotEquals('', $userSet->getPassword());
        $this->assertNotEquals(0, $userSet->getPassword());

        $this->assertNotEquals('object', gettype($user->getPassword()));
        $this->assertEquals('string', gettype($user->getPassword()));
        $this->assertEquals('0a', $user->getPassword());
        $this->assertNotEquals('', $user->getPassword());
        $this->assertNotEquals(0, $user->getPassword());
    }

    /**
     * Testing the roles.
     */
    public function testRoles()
    {
        $user = new User();
        $user->setLogin('login');

        $roles   = ['ROLE_ADMIN', 'ROLE_USER'];
        $userSet = $user->setRoles($roles);

        $this->assertEquals($roles, $userSet->getRoles());
        $this->assertEquals('object', gettype($userSet));
        $this->assertEquals('login', $userSet);
        $this->assertNotEquals([], $userSet->getRoles());
        $this->assertEquals('array', gettype($userSet->getRoles()));
        $this->assertNotEquals('object', gettype($userSet->getRoles()));

        $this->assertEquals($roles, $user->getRoles());
        $this->assertNotEquals([], $user->getRoles());
        $this->assertEquals('array', gettype($user->getRoles()));
        $this->assertNotEquals('object', gettype($user->getRoles()));
    }

    /**
     * Testing the string representation of the object.
     */
    public function testToString(): void
    {
        $user = new User();
        $user->setLogin('11-11');

        $this->assertEquals('11-11', $user);
        $this->assertEquals('object', gettype($user));
        $this->assertEquals('string', gettype($user->__toString()));
        $this->assertNotEquals(0, $user);
        $this->assertNotEquals('0', $user);
        $this->assertNotEquals('', $user);
        $this->assertNotEquals('1111', $user);
        $this->assertNotEquals([], $user);
    }

    /**
     * Testing the login.
     */
    public function testLogin(): void
    {
        $user = new User();
        $user->setLogin('11-11');

        $this->assertEquals('11-11', $user->getLogin());
        $this->assertEquals('string', gettype($user->getLogin()));
        $this->assertNotEquals('object', gettype($user->getLogin()));
        $this->assertNotEquals(0, $user->getLogin());
        $this->assertNotEquals('0', $user->getLogin());
        $this->assertNotEquals('', $user->getLogin());
        $this->assertNotEquals('1111', $user->getLogin());
        $this->assertNotEquals([], $user->getLogin());
    }

    /**
     * Testing if the user has a necessary role.
     */
    public function testHasRole(): void
    {
        $user  = new User();
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];

        $user->setRoles($roles);

        $this->assertEquals(true, $user->hasRole('ROLE_ADMIN'));
        $this->assertEquals(true, $user->hasRole('ROLE_USER'));
        $this->assertNotEquals(true, $user->hasRole('ROLE_MANAGER'));
        $this->assertEquals('boolean', gettype($user->hasRole('ROLE_ADMIN')));
        $this->assertNotEquals('bool', gettype($user->hasRole('ROLE_ADMIN')));
        $this->assertNotEquals('string', gettype($user->hasRole('ROLE_ADMIN')));
        $this->assertNotEquals('integer', gettype($user->hasRole('ROLE_ADMIN')));
    }

    /**
     * Testing if the user has the only necessary role.
     */
    public function testHasOnlyRole(): void
    {
        $user       = new User();
        $manyRoles  = ['ROLE_ADMIN', 'ROLE_USER'];
        $singleRole = ['ROLE_ADMIN'];

        $user->setRoles($manyRoles);
        $this->assertEquals(false, $user->hasOnlyRole('ROLE_ADMIN'));
        $this->assertEquals(false, $user->hasOnlyRole('ROLE_USER'));
        $this->assertEquals('boolean', gettype($user->hasOnlyRole('ROLE_ADMIN')));
        $this->assertNotEquals('bool', gettype($user->hasOnlyRole('ROLE_ADMIN')));
        $this->assertNotEquals('string', gettype($user->hasOnlyRole('ROLE_ADMIN')));
        $this->assertNotEquals('integer', gettype($user->hasOnlyRole('ROLE_ADMIN')));

        $user->setRoles($singleRole);
        $this->assertEquals(true, $user->hasOnlyRole('ROLE_ADMIN'));
        $this->assertEquals(false, $user->hasOnlyRole('ROLE_USER'));
    }

    /**
     * Testing the serialization of the object.
     *
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $id       = '11-aa';
        $login    = 'login';
        $password = 'password';
        $roles    = ['ROLE_ADMIN', 'ROLE_USER'];
        $token    = '11-aa';
        $until    = new DateTime();
        $object   = [$id, $login, $password, $roles, $token, $until];

        $serialized      = serialize($object);
        $wrongSerialized = serialize([$object]);
        $jsonEncoded     = json_encode($object);

        $user = new User();
        $user->setid($id);
        $user->setLogin($login);
        $user->setPassword($password);
        $user->setRoles($roles);
        $user->setToken($token);
        $user->setUntil($until);

        $serializedObject   = $user->serialize();
        $unserializedObject = $user->unserialize($serializedObject);

        $this->assertEquals($serialized, $serializedObject);
        $this->assertNotEquals($wrongSerialized, $serializedObject);
        $this->assertNotEquals($jsonEncoded, $serializedObject);
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($login, $user->getLogin());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
        $this->assertEquals($token, $user->getToken());
        //$this->assertEquals($until, $user->getUntil());
        $this->assertEquals('NULL', gettype($unserializedObject));
    }

    /**
     * Testing the username.
     */
    public function testUsername(): void
    {
        $user = new User();
        $user->setUsername('11-11');

        $this->assertEquals('11-11', $user->getUsername());
        $this->assertEquals('string', gettype($user->getUsername()));
        $this->assertNotEquals('object', gettype($user->getUsername()));
        $this->assertNotEquals(0, $user->getUsername());
        $this->assertNotEquals('0', $user->getUsername());
        $this->assertNotEquals('', $user->getUsername());
        $this->assertNotEquals('1111', $user->getUsername());
        $this->assertNotEquals([], $user->getUsername());
    }

    /**
     * Testing the documents.
     *
     * @throws Exception
     */
    public function testDocuments(): void
    {
        $user     = new User();
        $document = new Document();
        $document->setId('11-11');
        //$document->setPayload('payload');
        $documents = [$document, $document];

        $user->setDocuments($documents);

        $this->assertEquals((object) $documents, $user->getDocuments());
        $this->assertNotEquals($document, $user->getDocuments());
        $this->assertNotEquals([], $user->getDocuments());
    }
}
