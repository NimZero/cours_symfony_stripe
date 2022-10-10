<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $paid = false;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\Column(nullable: true)]
    private array $shipping_details = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ShippingRate $shipping_option = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getShippingDetails(): array
    {
        return $this->shipping_details;
    }

    public function setShippingDetails(?array $shipping_details): self
    {
        $this->shipping_details = $shipping_details;

        return $this;
    }

    public function getShippingOption(): ?ShippingRate
    {
        return $this->shipping_option;
    }

    public function setShippingOption(?ShippingRate $shipping_option): self
    {
        $this->shipping_option = $shipping_option;

        return $this;
    }
}
