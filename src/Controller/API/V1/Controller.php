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

namespace App\Controller\API\V1;

use Datetime;
use Exception;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * The common API v1 controller.
 */
class Controller extends AbstractController
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Response the HTTP_UNAUTHORIZED status.
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function responseHttpUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code'    => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response the HTTP_BAD_REQUEST status.
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function responseHttpBadRequest(string $message = 'Bad request'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code'    => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Response the HTTP_NOT_FOUND status.
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function responseHttpNotFound(string $message = 'Not Found'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code'    => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Response the HTTP_FORBIDDEN status.
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function responseHttpForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code'    => Response::HTTP_FORBIDDEN,
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * Response the HTTP_FORBIDDEN status.
     *
     * @param string $message
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function responseHttpInternalServerError(string $message = 'Internal Server Error'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get user by token.
     *
     * @param string $token
     *
     * @throws Exception
     *
     * @return User | null
     */
    protected function getUserByToken(string $token): ?User
    {
        /** @var User $user */
        $user = $this->em->getRepository('App:User')->createQueryBuilder('u')
            //->where('u.until > :now')->setParameter('now', new Datetime())
            ->andWhere('u.token = :token')->setParameter('token', $token)
            ->getQuery()->getOneOrNullResult();

        return $user;
    }

    /**
     * Check if the token is valid.
     *
     * @param User $user
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function isValidUntil(User $user): bool
    {
        return new Datetime() < $user->getUntil();
    }

    /**
     * Get the Bearer token from the header and find the user.
     *
     * @param Request $request
     *
     * @return User | JsonResponse | null
     */
    protected function getUserByAuthorizationHeader(Request $request): ?User
    {
        $authorization = $request->headers->get('Authorization');

        if (!$authorization) {
            return null;
        }

        $authorization = explode(' ', $authorization);

        if (2 !== count($authorization)) {
            return null;
        }

        if (!isset($authorization[0]) or !isset($authorization[1])) {
            return null;
        }

        $bearer = $authorization[0];

        if ('Bearer' !== $bearer) {
            return null;
        }

        $token = $authorization[1];

        try {
            return $this->getUserByToken($token);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @var EntityManager
     */
    protected EntityManager $em;
}
