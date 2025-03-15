<?php

namespace Controllers;

use Services\OrderService;
use Models\OrderDTO;
use Exception;

class OrderController extends Controller
{
    private $orderService;

    function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function placeOrder()
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "customer") {
                $this->respondWithError(403, "Forbidden: Only customer can place a order.");
                return;
            }
            $data = $this->createObjectFromPostedJson("Models\\OrderDTO");
            $order = $this->orderService->placeOrder($data);

            if (!$order) {
                throw new Exception("Order placement failed.");
            }

            $this->respond(["message" => "Order placed successfully!", "orderId" => $order->getId()]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAll()
    {
        try {
            $user = $this->authenticate();

            $offset = NULL;
            $limit = NULL;
            $status= null;

            if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
                $offset = $_GET["offset"];
            }
            if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
                $limit = $_GET["limit"];
            }
            if (isset($_GET["status"]) && is_string($_GET["status"])) {
                $status = $_GET["status"];
            }
            $orders = $this->orderService->getAll($user, $offset, $limit, $status);
            $this->respond($orders);
        } catch (Exception $e) {
        }
    }

    public function getOrderItemsByOrderId(int $orderId)
    {
        try {
            $user = $this->authenticate();
            $items = $this->orderService->getOrderItemsByOrderId($orderId);

            if (empty($items)) {
                $this->respondWithError(404, "No items found for this order.");
                return;
            }
    
            // ✅ Check if each item is valid before serializing
            $serializedItems = array_filter(array_map(fn($item) => $item instanceof \Models\OrderItem ? $item->jsonSerialize() : null, $items));
    
            $this->respond(["items" => $serializedItems]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function update($id) {
        try {
            $user = $this->authenticate();
    
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can update orders.");
                return;
            }

            $orderUpdateDTO = $this->createObjectFromPostedJson("Models\\OrderUpdateDTO");
            $order = $this->orderService->update($orderUpdateDTO, $id);
    
            $this->respond($order);
    
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
    
    public function getAdminStats() {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can access stats.");
                return;
            }
    
            $stats = $this->orderService->getAdminStats();
            $this->respond($stats);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }    

}
?>