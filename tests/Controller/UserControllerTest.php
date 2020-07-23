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

namespace App\Tests\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

/**
 * The class for the testing the User controller.
 *
 * @category Class
 *
 * @license  GNU General Public License version 2 or later
 */
class UserControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Setting up the environment.
     */
    public function setUp(): void
    {
        self::bootKernel();

        $this->em     = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->client = static::$kernel->getContainer()->get('test.client');
    }

    /**
     * Testing the creation of users with valid tokens.
     *
     * @throws Exception
     */
    public function testCreateUsersWithValidTokens(): void
    {
        $loginsValidToken = ['test_user_valid_token_1', 'test_user_valid_token_2'];

        foreach ($loginsValidToken as $login) { /** @var User $user */
            $user = $this->em->getRepository('App:User')->findOneBy([
                'login' => $login,
            ]);

            if (!$user) {
                $user = new User();
                $user->setLogin($login);
                $user->setRoles(['ROLE_USER']);
                $user->setToken(sha1(random_bytes(0xFFFF)));
                $encoder  = new NativePasswordEncoder(null, null, null, PASSWORD_BCRYPT);
                $password = $encoder->encodePassword($user, $login);
                $user->setPassword($password);
            }

            $user->setUntil(new DateTime('Now + 1 Hour'));

            $this->em->persist($user);
            $this->em->flush();

            $this->assertTrue($user->getUntil() > new DateTime('Now'));
            $this->assertTrue(in_array($user->getLogin(), $loginsValidToken));
        }
    }

    /**
     * Testing if the token has been prolonged.
     *
     * @throws Exception
     */
    public function testTokenProlongation(): void
    {
        $this->testCreateUsersWithValidTokens();

        $uri = '/api/v1/login';

        $client = static::$kernel->getContainer()->get('test.client');
        $client->request('POST', $uri, [], [], $this->getServerHeaders());

        /** @var User $user */
        $user = $this->em->getRepository('App:User')->findOneBy(['login' => 'test_user_valid_token_1']);

        $this->assertTrue(new DateTime('Now') < $user->getUntil());
        $this->assertTrue($user->getUntil() <= new DateTime('Now + 1 Hour'));
        $this->assertTrue(new DateTime('Now + 59 Minutes') <= $user->getUntil());
    }

    /**
     * Testing the creation of users with invalid tokens.
     *
     * @throws Exception
     */
    public function testCreateUsersWithInvalidTokens(): void
    {
        $loginsInvalidToken = ['test_user_invalid_token_1', 'test_user_invalid_token_2'];

        foreach ($loginsInvalidToken as $login) { /** @var User $user */
            $user = $this->em->getRepository('App:User')->findOneBy([
                'login' => $login,
            ]);

            if (!$user) {
                $user = new User();
                $user->setLogin($login);
                $user->setRoles(['ROLE_USER']);
                $user->setToken(hash('sha512', random_bytes(0xFFFF)));
                $encoder  = new NativePasswordEncoder(null, null, null, PASSWORD_BCRYPT);
                $password = $encoder->encodePassword($user, $login);
                $user->setPassword($password);
            }

            $user->setUntil(new DateTime('Now - 1 Year'));

            $this->em->persist($user);
            $this->em->flush();

            $this->assertTrue($user->getUntil() < new DateTime('Now'));
            $this->assertTrue(in_array($user->getLogin(), $loginsInvalidToken));
        }
    }

    /**
     * Testing the login.
     *
     * @throws Exception
     */
    public function testLogin(): void
    {
        $uri = '/api/v1/login';

        /** @var User $user */
        $user = $this->em->getRepository('App:User')->findOneBy(['login' => 'test_user_valid_token_1']);

        $this->assertTrue(isset($user));

        $userRequest = ['login' => $user->getLogin()];

        $userRequestJson = json_encode($userRequest);

        $this->client->request('POST', $uri, [], [], [], $userRequestJson);

        $response        = $this->client->getResponse();
        $responseContent = $response->getContent();
        $responseContent = (object) json_decode($responseContent);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($user->getLogin(), $responseContent->user);
        $this->assertEquals($user->getToken(), $responseContent->token);
        $this->assertEquals($user->getUntil()->format('U'), $responseContent->until);

        $this->client->request('POST', $uri, [], [], [], $userRequestJson);

        $response2        = $this->client->getResponse();
        $responseContent2 = $response2->getContent();
        $responseContent2 = (object) json_decode($responseContent2);

        $this->assertTrue($responseContent2->until >= $responseContent->until);
        $this->assertNotEquals($responseContent2->token, $responseContent->token);
    }

    /**
     * Testing the login of nonexistent user.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testLoginUserNonexistent(): void
    {
        $uri = '/api/v1/login';

        /** @var User $user */
        $user = $this->em->getRepository('App:User')->findOneBy(['login' => 'Nonexistent user']);

        $this->assertEmpty($user);

        $userRequest = ['login' => 'Nonexistent user'];

        $userRequestJson = json_encode($userRequest);

        $this->client->request('POST', $uri, [], [], [], $userRequestJson);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $this->client->request('POST', $uri, [], [], $this->getServerHeaders());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * Get server headers.
     *
     * @return array
     */
    protected function getServerHeaders(): array
    {
        return [
            'HTTP_Accept'       => 'application/json',
            'HTTP_Content-Type' => 'application/json',
        ];
    }

    /**
     * Cleaning up after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;

        $this->client = null;
    }
}
