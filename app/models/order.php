<?php

namespace Models;

class Order implements \JsonSerializable
{
    private int $id;
    private User $user;
    private float $total_price;
    private string $status;
    private string $created_at;

    public function __construct(User $user, float $total_price, string $status = 'pending', string $created_at = '')
    {
        $this->user = $user;
        $this->total_price = $total_price;
        $this->status = $status;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getUserId(): User
    {
        return $this->user;
    }
    public function getTotalPrice(): float
    {
        return $this->total_price;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    public function jsonSerialize(): array
    {
        return [
            'order_id' => $this->id,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'user_name' => $this->user->getName()
        ];
    }
}
?>