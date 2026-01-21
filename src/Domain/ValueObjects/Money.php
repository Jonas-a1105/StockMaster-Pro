<?php
namespace App\Domain\ValueObjects;

class Money {
    private $amount;
    private $currency;

    public function __construct(float $amount, string $currency = 'USD') {
        if ($amount < 0) {
            throw new \InvalidArgumentException("El monto no puede ser negativo.");
        }
        $this->amount = round($amount, 2);
        $this->currency = $currency;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function multiply(float $multiplier): self {
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function add(Money $other): self {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("No se pueden sumar diferentes monedas.");
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function format(): string {
        $symbol = $this->currency === 'USD' ? '$' : 'Bs. ';
        return $symbol . number_format($this->amount, 2);
    }

    public function __toString(): string {
        return (string)$this->amount;
    }
}
