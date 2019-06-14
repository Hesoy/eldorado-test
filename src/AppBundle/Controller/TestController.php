<?php
declare(strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Dto\Test;
use AppBundle\Form\TestType;
use AppBundle\Model\TestManager;
use AppBundle\Repository\TestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class NeoController
 *
 * @Route("/test", name="test_")
 *
 * @author hesoy s.shmygol@gmail.com
 * @package AppBundle\Controller
 */
class TestController extends Controller
{
    /** @var TestRepository */
    private $testRepository;

    /** @var TestManager */
    private $testManager;

    public function __construct(TestManager $testManager, TestRepository $testRepository)
    {
        $this->testManager = $testManager;
        $this->testRepository = $testRepository;
    }

    /**
     * @Route("/{id}", name="get", methods={"GET"}, requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getAction(int $id): JsonResponse
    {
        $test = $this->testRepository->find($id);

        if (null === $test) {
            return $this->json(['message' => \sprintf('Cannot find test by id \'%s\'', $id)], Response::HTTP_NOT_FOUND);
        }

        return $this->json($test);
    }

    /**
     * @Route("/gets", name="gets", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getsAction(Request $request): JsonResponse
    {
        $defaultPage = 1;
        $page = $request->query->getInt('page', $defaultPage);
        if ($page < 1) {
            $page = $defaultPage;
        }
        $defaultPerPage = 10;
        $perPage = $request->query->getInt('per_page', $defaultPerPage);
        if ($perPage < 1) {
            $perPage = $defaultPerPage;
        }
        $offset = ($page * $perPage) - $perPage;

        $tests = $this->testRepository->findBy([], [], $perPage, $offset);
        $totalRows = $this->testRepository->getTotalRows();
        $nextLink = null;
        $prevLink = null;
        if ($totalRows > ($page * $perPage)) {
            $nextLink = $this->generateUrl(
                'test_gets',
                ['page' => $page + 1, 'per_page' => $perPage],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        if ($page !== 1) {
            $prevLink = $this->generateUrl(
                'test_gets',
                ['page' => $page - 1, 'per_page' => $perPage],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $response = [
            'data' => $tests,
            'total_rows' => $totalRows,
            'page' => $page,
            'per_page' => $perPage,
        ];

        if (null !== $nextLink) {
            $response['next_link'] = $nextLink;
        }

        if (null !== $prevLink) {
            $response['prev_link'] = $prevLink;
        }

        return $this->json($response);
    }

    /**
     * @Route("/", name="post", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        $testDto = new Test();
        $formType = $this->createForm(TestType::class, $testDto, ['csrf_protection' => false]);

        $formType->submit($data);
        if ($formType->isSubmitted() && $formType->isValid()) {
            $test = $this->testManager->create($testDto);
            $this->testManager->save($test);

            return $this->json($test, Response::HTTP_CREATED);
        } else {
            return $this->json(['message' => (string)$formType->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAction(int $id): JsonResponse
    {
        /** @var \AppBundle\Entity\Test $test */
        $test = $this->testRepository->find($id);

        if (null === $test) {
            return $this->json(['message' => \sprintf('Cannot find test by id \'%s\'', $id)], Response::HTTP_NOT_FOUND);
        }

        $this->testManager->remove($test);

        return $this->json(['message' => \sprintf('Test with id \'%s\' was deleted', $id)]);
    }
}