<?php

namespace Services;

use Models\Order;
use Models\OrderItem;
use Models\OrderDTO;
use Repositories\OrderRepository;
use Repositories\UserRepository;
use Repositories\ProductRepository;
use Models\User;
use Exception;

class OrderService {
    private $orderRepository;
    private  $userRepository;
    private  $productRepository;

    function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->userRepository = new UserRepository();
        $this->productRepository = new ProductRepository();
    }

    public function placeOrder(OrderDTO $orderDTO): ?Order {
        try {
            $user = $this->userRepository->getOne($orderDTO->getUserId());
            if (!$user) {
                throw new Exception("User not found.");
            }

            // Create Order Model
            $order = new Order($user, $orderDTO->getTotalPrice());
            $orderId = $this->orderRepository->insertOrder($order);

            if (!$orderId) {
                throw new Exception("Failed to create order.");
            }
            $order->setId($orderId);
            // Insert order items
            foreach ($orderDTO->getItems() as $item) {
                $product = $this->productRepository->getOne($item['product_id']);
                if (!$product) {
                    throw new Exception("Invalid product ID: " . $item['product_id']);
                }

                $product = $this->productRepository->getOne($item['product_id']);
                $orderItem = new OrderItem($order, $product, $item['quantity'], $product->getPrice());
                $this->orderRepository->insertOrderItem($orderItem, $orderId);
            }

            return $order;
        } catch (Exception $e) {
            error_log("Error placing order: " . $e->getMessage());
            return null;
        }
    }

    public function getAll($user, $offset = NULL, $limit = NULL, $status = null) {
        return $this->orderRepository->getAll($user, $offset, $limit,$status);
    }

    public function getOrderItemsByOrderId(int $orderId): array {
        $items = $this->orderRepository->getOrderItemsByOrderId($orderId);
        return $items;
    }    
    
    public function update($orderUpdateDTO, $id){
        return $this->orderRepository->update($orderUpdateDTO, $id);
    }

    public function getOne($id){
        return $this->orderRepository->getOne($id);
    }

    public function getAdminStats(): array {
        return $this->orderRepository->getAdminStats();
    }
}
?>
