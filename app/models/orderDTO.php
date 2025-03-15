<?php

namespace Models;

class OrderDTO {
    private int $user_id;
    private float $total_price;
    private array $items; // Array of product IDs & quantities

    public function __construct(int $user_id, float $total_price, array $items) {
        $this->user_id = $user_id;
        $this->total_price = $total_price;
        $this->items = $items;
    }

    public function getUserId(): int { return $this->user_id; }
    public function getTotalPrice(): float { return $this->total_price; }
    public function getItems(): array { return $this->items; }
}
?>
