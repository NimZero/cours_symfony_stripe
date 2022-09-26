<?php

namespace App\Entity;

use App\Repository\ProductToGroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductToGroupRepository::class)]
class ProductToGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $stripeProductId = null;

    #[ORM\Column(length: 255)]
    private ?string $symfony_group = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(string $stripeProductId): self
    {
        $this->stripeProductId = $stripeProductId;

        return $this;
    }

    public function getSymfonyGroup(): ?string
    {
        return $this->symfony_group;
    }

    public function setSymfonyGroup(string $symfony_group): self
    {
        $this->symfony_group = $symfony_group;

        return $this;
    }
}
