<?php

namespace Controllers;

use Exception;
use Services\CategoryService;

class CategoryController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new CategoryService();
    }

    public function getAll()
    {
        $offset = NULL;
        $limit = NULL;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $categories = $this->service->getAll($offset, $limit);

        $this->respond($categories);
    }

    public function getOne($id)
    {
        $category = $this->service->getOne($id);

        if (!$category) {
            $this->respondWithError(404, "Category not found");
            return;
        }

        $this->respond($category);
    }

    public function create()
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can create categories.");
                return;
            }
            $category = $this->createObjectFromPostedJson("Models\\Category");
            $this->service->insert($category);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($category);
    }

    public function update($id)
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can update categories.");
                return;
            }
            $category = $this->createObjectFromPostedJson("Models\\Category");
            $this->service->update($category, $id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($category);
    }

    public function delete($id)
    {
        try {
            $user = $this->authenticate();
            if ($user->role !== "admin") {
                $this->respondWithError(403, "Forbidden: Only admins can delete categories.");
                return;
            }
            $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }
}
