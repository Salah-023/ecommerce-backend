<?php

namespace Repositories;

use Models\Order;
use Models\OrderItem;
use Models\User;
use Models\Category;
use Models\Product;
use PDO;
use Exception;
use PDOException;

class OrderRepository extends Repository
{

    public function insertOrder(Order $order): ?int
    {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO orders (user_id, total_price, status, created_at) 
                VALUES (:user_id, :total_price, :status, :created_at)
            ");

            // Use bindValue instead of bindParam (for consistency with UserRepository)
            $stmt->bindValue(':user_id', $order->getUserId()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':total_price', $order->getTotalPrice(), PDO::PARAM_STR); // FLOAT values should use STR in PDO
            $stmt->bindValue(':status', $order->getStatus(), PDO::PARAM_STR);
            $stmt->bindValue(':created_at', $order->getCreatedAt(), PDO::PARAM_STR);

            $stmt->execute();

            // Ensure insert was successful
            if ($stmt->rowCount() > 0) {
                return $this->connection->lastInsertId();
            } else {
                throw new Exception("Order insertion failed.");
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    public function insertOrderItem(OrderItem $orderItem, $orderId): bool
    {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (:order_id, :product_id, :quantity, :price)
            ");

            $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindValue(':product_id', $orderItem->getProduct()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':quantity', $orderItem->getQuantity(), PDO::PARAM_INT);
            $stmt->bindValue(':price', $orderItem->getPrice(), PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($user, ?int $offset = null, ?int $limit = null, ?string $status = null): array {
        try {
            $query = "SELECT orders.*, users.name as user_name FROM orders
                      INNER JOIN users ON orders.user_id = users.id";
    
            $params = [];
    
            if ($user->role === 'customer') {
                $query .= " WHERE orders.user_id = :user_id";
                $params[':user_id'] = $user->user_id;
            }
    
            if ($status !== null) {
                $query .= ($user->role === 'customer' ? " AND" : " WHERE") . " orders.status = :status";
                $params[':status'] = $status;
            }
    
            if ($limit !== null && $offset !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }
    
            $stmt = $this->connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
    
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            return [];
        }
    }
    

    public function update($orderUpdateDTO, $id)
    {
        try {
            // ✅ Dynamically build the SQL query
            $updates = [];
            $params = [':id' => $id];

            if ($orderUpdateDTO->getTotalPrice() !== null) {
                $updates[] = "total_price = :total_price";
                $params[':total_price'] = $orderUpdateDTO->getTotalPrice();
            }

            if ($orderUpdateDTO->getStatus() !== null) {
                $updates[] = "status = :status";
                $params[':status'] = $orderUpdateDTO->getStatus();
            }

            if (empty($updates)) {
                throw new Exception("No valid fields provided for update.");
            }

            $query = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = :id";
            $stmt = $this->connection->prepare($query);

            //Bind parameters dynamically
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_STR : PDO::PARAM_STR);
            }

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception("No record updated. Order with ID $id might not exist.");
            }

            return $this->getOne($id); // ✅ Fetch and return the updated order

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }


    public function getOne($id): ?Order
    {
        try {
            $query = "SELECT orders.*, users.name AS user_name, users.email AS user_email, users.role AS user_role 
                      FROM orders 
                      INNER JOIN users ON orders.user_id = users.id 
                      WHERE orders.id = :id";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                error_log("Order with ID $id not found.");
                return null;
            }

            return $this->rowToOrder($row); // Convert row to Order object

        } catch (PDOException $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return null;
        }
    }


    private function rowToOrder(array $row): Order
    {
        $user = new User($row['user_name'], $row['user_email'], "", $row['user_role']);
        $user->setId($row['user_id']);

        $order = new Order($user, $row['total_price'], $row['status'], $row['created_at']);
        $order->setId($row['id']);

        return $order;
    }


    public function getOrderItemsByOrderId(int $orderId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    oi.id AS order_item_id,
                    oi.quantity,
                    oi.price,
                    p.id AS product_id, p.name AS product_name, p.description, p.price AS product_price, p.stock, p.image_url,
                    c.id AS category_id, c.name AS category_name,
                    o.id AS order_id, o.user_id, o.total_price, o.status, o.created_at,
                    u.id AS user_id, u.name AS user_name, u.email, u.role
                FROM order_items oi
                INNER JOIN products p ON oi.product_id = p.id
                INNER JOIN categories c ON p.category_id = c.id
                INNER JOIN orders o ON oi.order_id = o.id
                INNER JOIN users u ON o.user_id = u.id
                WHERE oi.order_id = :order_id
            ");

            $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();

            $orderItems = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $orderItems[] = $this->rowToOrderItem($row);
            }

            return $orderItems;
        } catch (Exception $e) {
            error_log("Error fetching order items: " . $e->getMessage());
            return [];
        }
    }


    private function rowToOrderItem(array $row): OrderItem
    {
        $user = new User($row['user_name'], $row['email'], "", $row['role']);
        $user->setId($row['user_id']);

        $order = new Order($user, $row['total_price'], $row['status'], $row['created_at']);
        $order->setId($row['order_id']);

        $category = new Category($row['category_name']);
        $category->setId($row['category_id']);

        $product = new Product(
            $row['product_name'],
            $row['description'],
            $row['product_price'],
            $row['stock'],
            $row['image_url'],
            $category
        );
        $product->setId($row['product_id']);

        $orderItem = new OrderItem($order, $product, $row['quantity'], $row['price']);
        $orderItem->setId($row['order_item_id']);


        return $orderItem;
    }

    public function getAdminStats(): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    SUM(total_price) AS totalRevenue,
                    COUNT(*) AS totalOrders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pendingOrders,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processingOrders,
                    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS shippedOrders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS deliveredOrders
                FROM orders
            ");
    
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    

}
?>