<?php

namespace Models;

class Cart {
    private int $id;
    private User $user;
    private Product $product;
    private int $quantity;
    private string $created_at;

    public function __construct(User $user, Product $product, int $quantity, string $created_at = '') {
        $this->user_id = $user;
        $this->product_id = $product;
        $this->quantity = $quantity;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): User { return $this->user; }
    public function getProductId(): Product { return $this->product; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCreatedAt(): string { return $this->created_at; }

    // Setters
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
}
?>
