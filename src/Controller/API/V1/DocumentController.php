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
use App\Entity\User;
use App\Entity\Status;
use App\Entity\Document;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\ORMException;
use App\Adapter\DocumentAdapter;
use Doctrine\ORM\OptimisticLockException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

/**
 * The API v1 document controller.
 *
 * @Route("/api/v1/document")
 */
class DocumentController extends Controller
{
    /**
     * Create the draft document.
     *
     * @Route(methods={"POST"}, name="api__v1__document__create")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $user = $this->getUserByAuthorizationHeader($request);  /** @var User $user */

        if (!$user || !$this->isValidUntil($user)) {
            return $this->responseHttpUnauthorized();
        }

        try {
            $document = new Document();
            $document->setPayload((object) []);
            $document->setStatus($this->getStatusDraft());
            $document->setUser($user);

            $this->em->persist($document);
            $this->em->flush();

            return $this->responseDocumentHttpOk($document);
        } catch (ORMException $e) {
            return $this->responseHttpInternalServerError($e->getMessage());
        }
    }

    /**
     * Edit the document.
     *
     * @Route("/{id}", methods={"PATCH"}, name="api__v1__document__edit")
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws Exception
     *
     * @return Response
     */
    public function editAction(Request $request, string $id): Response
    {
        $user = $this->getUserByAuthorizationHeader($request); /** @var User $user */

        if (!$user || !$this->isValidUntil($user)) {
            return $this->responseHttpUnauthorized();
        }

        $requestContent  = $request->getContent();
        $requestDocument = json_decode($requestContent);

        if (empty($requestDocument->document->payload)) {
            return $this->responseHttpBadRequest('There is no a payload');
        }

        $requestPayload = (array) $requestDocument->document->payload;

        try {
            $document = $this->em->getRepository('App:Document')->find($id); /** @var Document $document */

            if (!$document) {
                return $this->responseHttpNotFound();
            }

            switch ($document->getStatus()) {
                case $this->getStatusPublished():
                    return $this->responseHttpBadRequest('The document had already been published');

                case $this->getStatusDraft():
                    if ($document->getUser() !== $user) {
                        return $this->responseHttpForbidden('User is not the owner of the document');
                    }

                    break;

                default:
                    return $this->responseHttpInternalServerError();
            }

            $pattern       = '/,\s*"[^"]+":null|"[^"]+":null,?/';
            $payloadMerged = json_encode(array_replace_recursive($document->getPayload(), $requestPayload));
            $payloadMerged = preg_replace($pattern, '', $payloadMerged);
            $payloadMerged = json_decode($payloadMerged);

            $document->setPayload((object) $payloadMerged);
            $this->em->persist($document);

            try {
                $this->em->flush();
            } catch (OptimisticLockException $e) {
                return $this->responseHttpInternalServerError($e->getMessage());
            }

            return $this->responseDocumentHttpOk($document);
        } catch (ORMException $e) {
            return $this->responseHttpInternalServerError($e->getMessage());
        }
    }

    /**
     * Publish the document.
     *
     * @Route("/{id}/publish", methods={"POST"}, name="api__v1__document__publish")
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws Exception
     *
     * @return Response
     */
    public function publishAction(Request $request, string $id): Response
    {
        $user = $this->getUserByAuthorizationHeader($request); /** @var User $user */

        if (!$user || !$this->isValidUntil($user)) {
            return $this->responseHttpUnauthorized();
        }

        try {
            $document = $this->em->getRepository('App:Document')->find($id); /** @var Document $document */

            if (!$document) {
                return $this->responseHttpNotFound();
            }

            if ($document->getUser() !== $user) {
                return $this->responseHttpForbidden('User is not the owner of the document');
            }

            $document->setStatus($this->getStatusPublished());
            $this->em->persist($document);

            try {
                $this->em->flush();
            } catch (OptimisticLockException $e) {
                return $this->responseHttpInternalServerError($e->getMessage());
            }

            return $this->responseDocumentHttpOk($document);
        } catch (ORMException $e) {
            return $this->responseHttpInternalServerError($e->getMessage());
        }
    }

    /**
     * Get the document.
     *
     * @Route("/{id}", methods={"GET"}, name="api__v1__document__get")
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws Exception
     *
     * @return Response
     */
    public function getAction(Request $request, string $id): Response
    {
        $user = $this->getUserByAuthorizationHeader($request); /** @var User $user */

        if ($user && !$this->isValidUntil($user)) {
            return $this->responseHttpUnauthorized();
        }

        $document = $this->em->getRepository('App:Document')->find($id); /** @var Document $document */

        if (!$document) {
            return $this->responseHttpNotFound();
        }

        $isOwnerCanGetOwnDocument = $document->getUser() === $user;

        $isNonOwnerOrAnonymousCanGetPublishedDocument = $document->getUser() !== $user
            && $document->getStatus() === $this->getStatusPublished();

        if ($isOwnerCanGetOwnDocument || $isNonOwnerOrAnonymousCanGetPublishedDocument) {
            return $this->responseDocumentHttpOk($document);
        }

        return $this->responseHttpForbidden('User is not the owner of the document');
    }

    /**
     * Get the document list.
     *
     * @Route(methods={"GET"}, name="api__v1__document__list")
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $page    = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 20);

        $user = $this->getUserByAuthorizationHeader($request); /** @var User $user */

        if ($user && !$this->isValidUntil($user)) {
            return $this->responseHttpUnauthorized();
        }

        $qb = $this->em->getRepository('App:Document')->createQueryBuilder('d')
            ->leftJoin('d.status', 's')
            ->orderBy('d.createdAt', 'DESC');

        if ($user) {
            $qb->andwhere('d.user = :user OR d.user != :user AND s.title = :title')
                ->setParameter('user', $user)
                ->setParameter('title', 'published');
        } else {
            $qb->andwhere('s.title = :title')->setParameter('title', 'published');
        }

        try {
            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
            $pagerfanta->setMaxPerPage($perPage);
            $pagerfanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            return $this->responseHttpNotFound($e->getMessage());
        }

        $currentPageResults = $pagerfanta->getCurrentPageResults();

        $documents = [];

        foreach ($currentPageResults as $document) { /* @var Document $document */
            $documents[] = new DocumentAdapter($document);
        }

        return new JsonResponse([
            'document'   => $documents,
            'pagination' => [
                'page'    => $pagerfanta->getCurrentPage(),
                'perPage' => $pagerfanta->getMaxPerPage(),
                'total'   => $pagerfanta->getNbPages(),
            ],
        ]);
    }

    /**
     * Get the document.
     *
     * @throws Exception
     *
     * @return Status|Response
     */
    protected function getStatusDraft(): ?Status
    {
        $status = $this->em->getRepository('App:Status')->findOneBy(['title' => 'draft']); /** @var Status $status */

        if (!$status) {
            return $this->responseHttpNotFound('"Draft" status has not been found');
        }

        return $status;
    }

    /**
     * Get the status object with a published status.
     *
     * @throws Exception
     *
     * @return Status|Response
     */
    protected function getStatusPublished(): ?Status
    {
        /** @var Status $status */
        $status = $this->em->getRepository('App:Status')->findOneBy(['title' => 'published']);

        if (!$status) {
            return $this->responseHttpNotFound('"Published" status has not been found');
        }

        return $status;
    }

    /**
     * Response the document with the HTTP_OK status.
     *
     * @param Document $document
     *
     * @throws Exception
     *
     * @return Response
     */
    protected function responseDocumentHttpOk(Document $document): Response
    {
        return new JsonResponse(['document' => new DocumentAdapter($document)]);
    }
}
