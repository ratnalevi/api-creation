<?php

namespace App\Entity;

use App\Repository\DelayedOrdersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DelayedOrdersRepository::class)]
class DelayedOrders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $order = null;

    #[ORM\Column]
    private ?\DateTime $expected_delivery_time = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    #[ORM\Column]
    private ?\DateTime $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    public function setOrder(Orders $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getExpectedDeliveryTime(): ?\DateTime
    {
        return $this->expected_delivery_time;
    }

    public function setExpectedDeliveryTime(\DateTime $expected_delivery_time): self
    {
        $this->expected_delivery_time = $expected_delivery_time;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
