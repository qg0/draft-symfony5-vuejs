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
use App\Entity\User;
use App\Entity\Document;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityManager;
use Swaggest\JsonSchema\Schema;
use Exception as ExceptionAlias;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\OptimisticLockException as OptimisticLockExceptionAlias;

/**
 * The class for the testing the Document controller.
 *
 * @category Class
 *
 * @license  GNU General Public License version 2 or later
 */
class DocumentControllerTest extends WebTestCase
{
    const USER_VALID_TOKEN_1 = 'test_user_valid_token_1';

    const USER_VALID_TOKEN_2 = 'test_user_valid_token_2';

    const USER_INVALID_TOKEN_1 = 'test_user_invalid_token_1';

    const USER_INVALID_TOKEN_2 = 'test_user_invalid_token_2';

    /**
     * @var KernelBrowser|null
     */
    protected $client;

    /**
     * @var EntityManager|null
     */
    protected $em;

    /**
     * @var string
     */
    protected string $documentSchemaUrl;

    /**
     * @var string
     */
    protected string $documentResponseSchemaUrl;

    /**
     * @var string
     */
    protected string $documentListResponseSchemaUrl;

    /**
     * Setting up the environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        self::bootKernel();

        $this->em     = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->client = static::$kernel->getContainer()->get('test.client');

        $nginxUri = $_ENV['NGINX_SCHEME'].'://'.$_ENV['NGINX_HOST'].':'.$_ENV['NGINX_PORT'];

        $this->documentSchemaUrl             = $nginxUri.$_ENV['DOCUMENT_SCHEMA_URL'];
        $this->documentResponseSchemaUrl     = $nginxUri.$_ENV['DOCUMENT_RESPONSE_SCHEMA_URL'];
        $this->documentListResponseSchemaUrl = $nginxUri.$_ENV['DOCUMENT_LIST_RESPONSE_SCHEMA_URL'];
    }

    /**
     * Testing if the document has not been created by an anonymous user.
     *
     * @return void
     */
    public function testCreateByAnonymous(): void
    {
        $uri = '/api/v1/document';
        $this->client->request('POST', $uri);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document has been created by the user with a valid token.
     *
     * @param int $userNumber
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     *
     * @return string
     */
    public function testCreateDraftDocumentByUserWithValidToken(int $userNumber = 1): string
    {
        $login = 'test_user_valid_token_'.$userNumber;

        $user = $this->getUserAndProlongateToken($login);
        $uri  = '/api/v1/document';

        $this->client->request('POST', $uri, [], [], $this->getHeaders($user));

        $document = $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());

        return (string) $document->id;
    }

    /**
     * Testing if the document has not been edited by an anonymous.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testEditByAnonymous(): void
    {
        $id  = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri = '/api/v1/document/'.$id;

        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('PATCH', $uri);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document without payload has not been edited by the user.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testNoEditWithNoPayloadByUser(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri  = '/api/v1/document/'.$id;
        $user = $this->getUserAndProlongateToken();

        $data = [$this->getJsonDataWithEmptyPayload(), $this->getJsonDataWithoutPayload()];

        foreach ($data as $bunch) {
            $this->client->request('PATCH', $uri, [], [], $this->getHeaders($user), $bunch);

            $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * Testing if the document has been edited by the user.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testEditByUser(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri  = '/api/v1/document/'.$id;
        $user = $this->getUserAndProlongateToken();

        $this->client->request('PATCH', $uri, [], [], $this->getHeaders($user), $this->getJsonData());

        $document = $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());

        $this->assertEquals('cunning', $document->payload->meta->type);
        $this->assertEquals('blue', $document->payload->meta->color);
        $this->assertEquals('below', $document->payload->meta->level);
        $this->assertEquals('two', $document->payload->meta->mark);
        $this->assertEquals('big', $document->payload->meta->size);

        $this->assertNotEquals('red', $document->payload->meta->color);
        $this->assertNotEquals('null', $document->payload->meta->level);
        $this->assertNotEquals('null', $document->payload->meta->size);
        $this->assertNotEquals('', $document->payload->meta->level);
        $this->assertNotEquals('', $document->payload->meta->size);

        $this->client->request('PATCH', $uri, [], [], $this->getHeaders($user), $this->getJsonDataWithNulls());

        $document = $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());

        $this->assertEquals('cunning', $document->payload->meta->type);
        $this->assertEquals('red', $document->payload->meta->color);
        $this->assertEquals('two', $document->payload->meta->mark);

        $this->assertNotEquals('blue', $document->payload->meta->color);
        $this->assertFalse(isset($document->payload->meta->level));
        $this->assertFalse(isset($document->payload->meta->size));
    }

    /**
     * Testing if the document has not been edited by a non-owner.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testNotEditedByNonOwner(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $user = $this->getUserAndProlongateToken(self::USER_VALID_TOKEN_2);
        $uri  = '/api/v1/document/'.$id;

        $this->client->request('PATCH', $uri, [], [], $this->getHeaders($user), $this->getJsonData());
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document has been published by the owner.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testPublish(): void
    {
        $id         = $this->testCreateDraftDocumentByUserWithValidToken();
        $uriPublish = '/api/v1/document/'.$id.'/publish';
        $user       = $this->getUserAndProlongateToken();

        foreach (['draft', 'published'] as $state) {
            $this->client->request('POST', $uriPublish, [], [], $this->getHeaders($user));
            $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());
        }

        $uri = '/api/v1/document/'.$id;
        $this->client->request('PATCH', $uri, [], [], $this->getHeaders($user), $this->getJsonData());
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document has not been published by a non-owner.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testNotPublishedByNonOwner(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri  = '/api/v1/document/'.$id.'/publish';
        $user = $this->getUserAndProlongateToken('test_user_valid_token_2');

        $this->client->request('POST', $uri, [], [], $this->getHeaders($user));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document has not been created by the user with an invalid token.
     *
     * @throws ORMException
     *
     * @return void
     */
    public function testCreateByUserWithInvalidToken(): void
    {
        $uri  = '/api/v1/document';
        $user = $this->getUserAndProlongateToken('test_user_valid_token_2');

        $authTypes = [
            'bearer',
            'basic',
            'hoba',
            'digest',
            'mutual',
            'aws4-hmac-sha256',
            'x-auth-token',
        ];

        $whiteSpaces = [
            '',
            ' ',
            '  ',
            '\t',
            '\f',
            '\v',
            '\r',
            '\n',
            '\r\n',
            '\0',
        ];

        $httpAuthorizationInvalids = [];

        foreach ($authTypes as $authType) {
            foreach ($whiteSpaces as $whiteSpaceBegin) {
                foreach ($whiteSpaces as $whiteSpaceEnd) {
                    $stringLower   = $whiteSpaceBegin.$authType.$whiteSpaceEnd;
                    $stringUpper   = $whiteSpaceBegin.strtoupper($authType).$whiteSpaceEnd;
                    $stringUCFirst = $whiteSpaceBegin.ucfirst($authType).$whiteSpaceEnd;

                    $httpAuthorizationInvalids[] = $stringLower;
                    $httpAuthorizationInvalids[] = $stringUpper;
                    $httpAuthorizationInvalids[] = $stringUCFirst;

                    $httpAuthorizationInvalids[] = $stringLower.$user->getToken();
                    $httpAuthorizationInvalids[] = $stringUpper.$user->getToken();

                    $bearerString = 'Bearer ' !== $stringUCFirst ? $stringUCFirst.$user->getToken() : '';

                    $httpAuthorizationInvalids[] = $bearerString;

                    $httpAuthorizationInvalids[] = $stringLower.$user->getToken().' '.$user->getToken();
                    $httpAuthorizationInvalids[] = $stringUpper.$user->getToken().' '.$user->getToken();
                    $httpAuthorizationInvalids[] = $stringUCFirst.$user->getToken().' '.$user->getToken();
                }
            }
        }

        foreach ($httpAuthorizationInvalids as $httpAuthorizationInvalid) {
            $serverHeaders = ['HTTP_Authorization' => $httpAuthorizationInvalid];

            $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
            $this->client->request('POST', $uri, [], [], $serverHeaders);

            $response = $this->client->getResponse(); /* @var Response $response */

            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        }

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['login' => 'test_user_invalid_token_1']);

        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('POST', $uri, [], [], $this->getHeaders($user));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the draft document has not been got by anonymous.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testGetDraftByAnonymous(): void
    {
        $id  = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri = '/api/v1/document/'.$id;

        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('GET', $uri, [], [], []);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document has been got by the user.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testGetDraftByUser(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $uri  = '/api/v1/document/'.$id;
        $user = $this->getUserAndProlongateToken();

        $this->client->request('GET', $uri, [], [], $this->getHeaders($user));

        $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());
    }

    /**
     * Testing if the published document has been got by the anonymous.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testGetPublishedByAnonymous(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $user = $this->getUserAndProlongateToken();

        $uriPublish = '/api/v1/document/'.$id.'/publish';
        $this->client->request('POST', $uriPublish, [], [], $this->getHeaders($user));
        $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());

        $uri          = '/api/v1/document/'.$id;
        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('GET', $uri, [], [], $this->getHeaders());
        $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());
    }

    /**
     * Testing if the document has not been got by non-owner.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testGetDraftByNonOwner(): void
    {
        $id   = $this->testCreateDraftDocumentByUserWithValidToken();
        $user = $this->getUserAndProlongateToken('test_user_valid_token_2');
        $uri  = '/api/v1/document/'.$id;

        $this->client->request('GET', $uri, [], [], $this->getHeaders($user));
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Testing if the document list has been got by the anonymous user.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testListAnonymous(): void
    {
        $user = $this->getUserAndProlongateToken();
        $this->removeAllDocuments($user);

        $user1DocumentIds = [];

        for ($i = 0; $i < 5; ++$i) {
            $user1DocumentIds[] = $this->testCreateDraftDocumentByUserWithValidToken();
        }

        $responseContentAnonymous = $this->getListResponse();
        $responseAnonymousIds     = $this->getResponseContentDocumentIds($responseContentAnonymous);

        $this->checkNextPage($responseContentAnonymous);

        foreach ($user1DocumentIds as $user1DocumentId) {
            $this->assertFalse(in_array($user1DocumentId, $responseAnonymousIds));

            $uriPublish   = '/api/v1/document/'.$user1DocumentId.'/publish';
            $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
            $this->client->request('POST', $uriPublish, [], [], $this->getHeaders($user));
        }

        $responseContentAnonymousPublished = $this->getListResponse();
        $responseIdsPublished              = $this->getResponseContentDocumentIds($responseContentAnonymousPublished);

        foreach ($user1DocumentIds as $user1DocumentId) {
            $this->assertTrue(in_array($user1DocumentId, $responseIdsPublished));
        }

        $this->checkNextPage($responseContentAnonymousPublished);
    }

    /**
     * Testing if the document list has been got by the user.
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ORMException
     *
     * @return void
     */
    public function testListUser(): void
    {
        $user1 = $this->getUserAndProlongateToken(self::USER_VALID_TOKEN_1);
        $this->removeAllDocuments($user1);

        $user2 = $this->getUserAndProlongateToken(self::USER_VALID_TOKEN_2);
        $this->removeAllDocuments($user2);

        $user1DocumentIds = [];

        for ($i = 0; $i < 5; ++$i) {
            $user1DocumentIds[] = $this->testCreateDraftDocumentByUserWithValidToken();
        }

        $responseContentUser1     = $this->getListResponse($user1);
        $responseDocumentIdsUser1 = $this->getResponseContentDocumentIds($responseContentUser1);

        foreach ($user1DocumentIds as $user1DocumentId) {
            $this->assertTrue(in_array($user1DocumentId, $responseDocumentIdsUser1));
        }

        $responseContentUser2     = $this->getListResponse($user2);
        $responseDocumentIdsUser2 = $this->getResponseContentDocumentIds($responseContentUser2);

        $this->checkNextPage($responseContentUser2);

        foreach ($user1DocumentIds as $user1DocumentId) {
            $this->assertFalse(in_array($user1DocumentId, $responseDocumentIdsUser2));

            $uriPublish = '/api/v1/document/'.$user1DocumentId.'/publish';
            $this->client->request('POST', $uriPublish, [], [], $this->getHeaders($user1));

            $this->getDocumentAndCheckSchemaHttpOK($this->client->getResponse());
        }

        $responseContentUser2Published = $this->getListResponse($user2);
        $responseDocumentIdsUser2      = $this->getResponseContentDocumentIds($responseContentUser2Published);

        foreach ($user1DocumentIds as $user1DocumentId) {
            $this->assertTrue(in_array($user1DocumentId, $responseDocumentIdsUser2));
        }

        $this->checkNextPage($responseContentUser2Published);
    }

    /**
     *  Testing if the user and their documents has been deleted.
     *
     * @throws InvalidValue
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     * @throws ExceptionAlias
     *
     * @return void
     */
    public function testRemoveUser(): void
    {
        $logins = [
            self::USER_VALID_TOKEN_1,
            self::USER_VALID_TOKEN_2,
            self::USER_INVALID_TOKEN_1,
            self::USER_INVALID_TOKEN_2,
        ];

        $this->testCreateDraftDocumentByUserWithValidToken(1);
        $this->testCreateDraftDocumentByUserWithValidToken(2);

        $documentIds = [];

        foreach ($logins as $login) {  /** @var User $user */
            $user = $this->em->getRepository(User::class)->findOneBy(['login' => $login]);

            foreach ($user->getDocuments() as $document) { /* @var Document $document */
                $documentIds[] = $document->getId();
            }

            $this->em->remove($user);
            $this->em->flush($user);

            /* @var User $userDeleted */
            $userDeleted = $this->em->getRepository(User::class)->findOneBy(['login' => $login]);
            $this->assertNull($userDeleted);
        }

        foreach ($documentIds as $id) {
            $documentDeleted = $this->em->getRepository(Document::class)->findOneBy(['id' => $id]);
            $this->assertNull($documentDeleted);
        }
    }

    /**
     * Get the list response.
     *
     * @param User|null $user
     *
     * @throws Exception
     *
     * @return object
     */
    protected function getListResponse(User $user = null): object
    {
        $uri = '/api/v1/document';

        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('GET', $uri, [], [], $user ? $this->getHeaders($user) : []);

        $response = $this->client->getResponse(); /** @var Response $response */
        $schema   = Schema::import($this->documentListResponseSchemaUrl);
        $schema->in(json_decode($response->getContent()));

        $responseContent = $response->getContent();
        $responseContent = (object) json_decode($responseContent);

        return $responseContent;
    }

    /**
     * Get the json test data without payload.
     *
     * @return string
     */
    protected function getJsonDataWithoutPayload(): string
    {
        return json_encode([
            'document' => [],
        ]);
    }

    /**
     * Get the json test data with empty payload.
     *
     * @return string
     */
    protected function getJsonDataWithEmptyPayload(): string
    {
        return json_encode([
            'document' => [
                'payload' => [],
            ],
        ]);
    }

    /**
     * Get the json test data.
     *
     * @return string
     */
    protected function getJsonData(): string
    {
        return json_encode([
            'document' => [
                'payload' => [
                    'actor' => 'The fox',
                    'meta'  => [
                        'type'  => 'cunning',
                        'color' => 'blue',
                        'level' => 'below',
                        'mark'  => 'two',
                        'size'  => 'big',
                    ],
                    'actions' => [
                        [
                            'action' => 'eat',
                            'actor'  => 'blob',
                        ],
                        [
                            'action' => 'run away',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get the json test data with null values.
     *
     * @return string
     */
    protected function getJsonDataWithNulls(): string
    {
        return json_encode([
            'document' => [
                'payload' => [
                    'actor' => 'The fox',
                    'meta'  => [
                        'type'  => 'cunning',
                        'color' => 'red',
                        'level' => null,
                        'mark'  => 'two',
                        'size'  => null,
                    ],
                    'actions' => [
                        [
                            'action' => 'eat',
                            'actor'  => 'blob',
                        ],
                        [
                            'action' => 'run away',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get the server headers with a Bearer token.
     *
     * @param User|null $user
     *
     * @return array
     */
    protected function getHeaders(User $user = null): array
    {
        $headers = [
            'HTTP_Accept'       => 'application/json',
            'HTTP_Content-Type' => 'application/json',
        ];

        if ($user) {
            $headers['HTTP_Authorization'] = 'Bearer '.$user->getToken();
        }

        return $headers;
    }

    /**
     * Get the server headers with a Bearer token.
     *
     * @param object $responseContent
     *
     * @return array
     */
    protected function getResponseContentDocumentIds(object $responseContent): array
    {
        $responseIds = [];

        foreach ($responseContent->document as $document) {
            $responseIds[] = $document->id;
        }

        return $responseIds;
    }

    /**
     * Remove all user documents.
     *
     * @param User $user
     *
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     *
     * @return void
     */
    protected function removeAllDocuments(User $user): void
    {
        foreach ($user->getDocuments() as $document) { /* @var Document $document */
            $this->em->remove($document);
        }

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Prolongate the user token.
     *
     * @param User $user
     *
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     * @throws ExceptionAlias
     *
     * @return void
     */
    protected function prolongateToken(User $user): void
    {
        $until = new DateTime('Now + 1 Hour');

        $user->setUntil($until);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Testing the next page in the list.
     *
     * @param object $responseContent
     *
     * @return void
     */
    protected function checkNextPage(object $responseContent): void
    {
        $this->assertTrue(isset($responseContent->pagination));
        $this->assertTrue(isset($responseContent->pagination->page));
        $this->assertTrue(isset($responseContent->pagination->perPage));
        $this->assertTrue(isset($responseContent->pagination->total));

        $totalPages    = $responseContent->pagination->total;
        $totalNextPage = ++$totalPages;

        $uri = '/api/v1/document?';
        $uri .= http_build_query(['page' => $totalNextPage]);

        $this->client = static::$kernel->getContainer()->get('test.client'); // New stateless instance
        $this->client->request('GET', $uri);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Check document schema.
     *
     * @param Response $response
     *
     * @throws Exception
     * @throws InvalidValue
     * @throws ObjectExceptionAlias
     *
     * @return object
     */
    protected function getDocumentAndCheckSchemaHttpOK(Response $response): object
    {
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $schema = Schema::import($this->documentResponseSchemaUrl);
        $schema->in($response);

        $responseContent = json_decode($response->getContent());

        $schema = Schema::import($this->documentSchemaUrl);
        $schema->in($responseContent->document);

        return $responseContent->document;
    }

    /**
     * Get the user by login and prolongate the token.
     *
     * @param string $login
     *
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     *
     * @return User
     */
    protected function getUserAndProlongateToken($login = self::USER_VALID_TOKEN_1): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['login' => $login]);
        $this->prolongateToken($user);

        return $user;
    }

    /**
     * Cleaning the environment.
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
