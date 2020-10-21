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

use Exception;
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
     * @var EntityManager
     */
    protected EntityManager $em;
}
