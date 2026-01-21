<?php
namespace App\Domain\ValueObjects;

class Quantity {
    private $value;

    public function __construct(int $value) {
        if ($value < 0) {
            throw new \InvalidArgumentException("La cantidad no puede ser negativa.");
        }
        $this->value = $value;
    }

    public function getValue(): int {
        return $this->value;
    }

    public function isZero(): bool {
        return $this->value === 0;
    }

    public function add(int $amount): self {
        return new self($this->value + $amount);
    }

    public function subtract(int $amount): self {
        if ($this->value < $amount) {
            throw new \InvalidArgumentException("Stock insuficiente.");
        }
        return new self($this->value - $amount);
    }

    public function __toString(): string {
        return (string)$this->value;
    }
}
