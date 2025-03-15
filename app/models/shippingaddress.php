<?php

namespace Models;

class ShippingAddress {
    private int $id;
    private User $user;
    private Order $order;
    private string $address_line1;
    private string $address_line2;
    private string $city;
    private string $postal_code;
    private string $country;

    public function __construct(User $user, Order $order, string $address_line1, string $address_line2, string $city, string $postal_code, string $country) {
        $this->user = $user;
        $this->order = $order;
        $this->address_line1 = $address_line1;
        $this->address_line2 = $address_line2;
        $this->city = $city;
        $this->postal_code = $postal_code;
        $this->country = $country;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): User { return $this->user; }
    public function getOrderId(): Order { return $this->order; }
    public function getAddressLine1(): string { return $this->address_line1; }
    public function getAddressLine2(): string { return $this->address_line2; }
    public function getCity(): string { return $this->city; }
    public function getPostalCode(): string { return $this->postal_code; }
    public function getCountry(): string { return $this->country; }
}
?>
