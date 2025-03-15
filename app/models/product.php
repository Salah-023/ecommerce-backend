<?php
namespace Models;

class Product implements \JsonSerializable {
    private int $id;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private string $image_url;
    private Category $category;
    private string $created_at;

    public function __construct(string $name, string $description, float $price, int $stock, string $image_url, Category $category, string $created_at = '') {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->image_url = $image_url;
        $this->category = $category;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getImageUrl(): string { return $this->image_url; }
    public function getCategory(): Category { return $this->category; }
    public function getCreatedAt(): string { return $this->created_at; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setImageUrl(string $image_url): void { $this->image_url = $image_url; }
    public function setCategoryId(Category $category): void { $this->category = $category; }

    public function jsonSerialize(): array {
        return [
            'product_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->image_url,
            'category' => $this->category
        ];
    }
}

?>