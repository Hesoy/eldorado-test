<?php
declare(strict_types = 1);

namespace AppBundle\Model;

use AppBundle\Entity\Test;
use AppBundle\Repository\TestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AppBundle\Dto\Test as TestDto;

/**
 * Class TestManager
 *
 * @author hesoy s.shmygol@gmail.com
 * @package AppBundle\Model
 */
class TestManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ValidatorInterface */
    private $validator;

    /** @var TestRepository */
    private $repository;

    public function __construct(
        TestRepository $repository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function create(TestDto $dto): Test
    {
        $test = new Test();
        $test->setName($dto->getName());

        return $test;
    }

    public function save(Test $test): void
    {
        $this->validate($test);

        $this->entityManager->persist($test);
        $this->entityManager->flush();
    }

    public function remove(Test $test): void
    {
        $this->entityManager->remove($test);
        $this->entityManager->flush();
    }

    private function validate(Test $test): void
    {
        $errors = $this->validator->validate($test);

        if (0 < \count($errors)) {
            throw new ValidatorException((string)$errors);
        }
    }
}