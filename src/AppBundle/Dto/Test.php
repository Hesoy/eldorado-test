<?php
declare(strict_types = 1);

namespace AppBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Test
 *
 * @author hesoy s.shmygol@gmail.com
 * @package AppBundle\Dto
 */
class Test
{

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}