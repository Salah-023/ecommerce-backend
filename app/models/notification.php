<?php

namespace Models;

class Notification {
    private int $id;
    private User $user;
    private string $message;
    private string $status;
    private string $created_at;

    public function __construct(User $user, string $message, string $status = 'unread', string $created_at = '') {
        $this->user_id = $user;
        $this->message = $message;
        $this->status = $status;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getMessage(): string { return $this->message; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->created_at; }

    // Setters
    public function setStatus(string $status): void { $this->status = $status; }
    public function setUser(User $user): void { $this->user = $user; }
    public function setMessage(string $message) : void { $this->message = $message;}

}
?>
