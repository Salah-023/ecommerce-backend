<?php
namespace Models;

class Category implements \JsonSerializable {
    private ? int $id;
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }
    

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setId(int $id): void { $this->id = $id; }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}

?>