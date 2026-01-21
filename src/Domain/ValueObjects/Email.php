<?php
namespace App\Domain\ValueObjects;

class Email {
    private $address;

    public function __construct(string $address) {
        $address = trim($address);
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El formato del email no es válido: $address");
        }
        $this->address = $address;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function equals(Email $other): bool {
        return strtolower($this->address) === strtolower($other->address);
    }

    public function __toString(): string {
        return $this->address;
    }
}
