<?php

namespace Controllers;

use Exception;
use Services\ProductService;

class ProductController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new ProductService();
    }

    public function getAll()
    {
        $offset = NULL;
        $limit = NULL;
        $category_id= NULL;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }
        if (isset($_GET["category_id"]) && is_numeric($_GET["category_id"])) {
            $category_id = $_GET["category_id"];
        }

        $products = $this->service->getAll($category_id,$offset, $limit);

        $this->respond($products);
    }

    public function getOne($id)
    {
        $product = $this->service->getOne($id);

        if (!$product) {
            $this->respondWithError(404, "Product not found");
            return;
        }

        $this->respond($product);
    }

    public function create()
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can create products.");
                return;
            }
            $product = $this->createObjectFromPostedJson("Models\\Product");
            $product = $this->service->insert($product);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($product);
    }

    public function update($id)
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can update products.");
                return;
            }
            $product = $this->createObjectFromPostedJson("Models\\Product");
            $product = $this->service->update($product, $id);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($product);
    }

    public function delete($id)
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can delete products.");
                return;
            }
            $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }
}
