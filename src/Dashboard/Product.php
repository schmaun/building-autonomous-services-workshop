<?php

namespace Dashboard;

use Common\Persistence\IdentifiableObject;

class Product implements IdentifiableObject
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $stockLevel = 0;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function stockLevel(): int
    {
        return $this->stockLevel;
    }

    public function increase(int $receivedQuantity): void
    {
        $this->stockLevel += $receivedQuantity;
    }

    public function decrease(int $deliveredQuantity): void
    {
        $this->stockLevel -= $deliveredQuantity;
    }
}