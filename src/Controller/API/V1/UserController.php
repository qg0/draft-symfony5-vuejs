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

use DateTime;
use Exception;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The API v1 user controller.
 *
 * @Route("/api/v1")
 */
class UserController extends Controller
{
    /**
     * Login the user.
     *
     * @Route("/login", methods={"POST"}, name="api__v1__user__login")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function loginAction(Request $request): Response
    {
        $requestContent = json_decode($request->getContent());

        if (!isset($requestContent->login)) {
            return new JsonResponse([
                'message' => 'There is no login',
                'code'    => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->em->getRepository('App:User')->findOneBy([
            'login' => $requestContent->login,
        ]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'There is no login',
                'code'    => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $user->setToken(sha1(random_bytes(1024)));
        $user->setUntil(new DateTime('Now + 1 Hour'));

        $this->em->persist($user);

        try {
            $this->em->flush();
        } catch (OptimisticLockException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'user'  => $user->getLogin(),
            'token' => $user->getToken(),
            'until' => $user->getUntil()->format('U'),
        ]);
    }
}
