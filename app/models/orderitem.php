<?php

namespace Models;

class OrderItem implements \JsonSerializable {
    private int $id;
    private Order $order;
    private Product $product;
    private int $quantity;
    private float $price;

    public function __construct(Order $order, Product $product, int $quantity, float $price) {
        $this->order = $order;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getOrder(): Order { return $this->order; }
    public function getProduct(): Product { return $this->product; }
    public function getQuantity(): int { return $this->quantity; }
    public function getPrice(): float { return $this->price; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    // public function setOrDER(Order $order): void { $this->order}

    public function jsonSerialize(): array {
        return [
            'order_item_id' => $this->id,
            'order' => $this->order->jsonSerialize(), // Full order data
            'product' => $this->product->jsonSerialize(), // Full product data
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
    
}
?>
