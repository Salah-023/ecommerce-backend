<?php

namespace Models;

class OrderUpdateDTO {
    private ?float $total_price = null;
    private ?string $status = null;

    public function __construct(?float $total_price = null, ?string $status = null) {
        $this->total_price = $total_price;
        $this->status = $status;
    }

    public function getTotalPrice(): ?float { return $this->total_price; }
    public function getStatus(): ?string { return $this->status; }
}

?>
