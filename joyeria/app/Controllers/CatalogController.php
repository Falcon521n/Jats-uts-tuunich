<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

final class CatalogController extends Controller
{
    public function index(): void
    {
        $categoryModel = new CategoryModel($this->pdo);
        $productsModel = new ProductModel($this->pdo);
        $categories = $categoryModel->all();
        $options = $productsModel->catalogOptions();

        $category = $_GET['categoria'] ?? '';
        $validCategories = array_column($categories, 'name');
        $category = is_string($category) && in_array($category, $validCategories, true) ? $category : '';
        $searchInput = $_GET['buscar'] ?? '';
        $typeInput = $_GET['tipo'] ?? '';
        $search = is_string($searchInput) ? trim(substr($searchInput, 0, 320)) : '';
        $type = is_string($typeInput) ? trim(substr($typeInput, 0, 80)) : '';
        if (!in_array($type, $options['types'], true)) {
            $type = '';
        }
        $selectedMaterials = $_GET['materiales'] ?? [];
        $selectedMaterials = is_array($selectedMaterials)
            ? array_values(array_intersect(
                $options['materials'],
                array_slice(array_filter($selectedMaterials, 'is_string'), 0, 12)
            ))
            : [];
        $filters = [
            'category' => $category,
            'search' => $search,
            'type' => $type,
            'materials' => $selectedMaterials,
        ];
        $perPage = 12;
        $totalProducts = $productsModel->countCatalog($filters);
        $totalPages = max(1, (int) ceil($totalProducts / $perPage));
        $page = min(positive_int($_GET['pagina'] ?? 1, 100000) ?? 1, $totalPages);
        $products = $productsModel->catalogPage($filters, $perPage, ($page - 1) * $perPage);

        $this->render('catalog/index', [
            'pageTitle' => 'Tuunich | Joyería con historia',
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'type' => $type,
            'types' => $options['types'],
            'materials' => $options['materials'],
            'selectedMaterials' => $selectedMaterials,
            'products' => $products,
            'totalProducts' => $totalProducts,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'page' => $page,
        ]);
    }

    public function product(): void
    {
        $id = positive_int($_GET['id'] ?? null);
        $product = $id === null ? null : (new ProductModel($this->pdo))->findActive($id);
        if ($product === null) {
            http_response_code(404);
            $this->render('errors/not-found', ['pageTitle' => 'Producto no encontrado | Tuunich']);
            return;
        }

        $this->render('catalog/product', [
            'pageTitle' => $product['name'] . ' | Tuunich',
            'product' => $product,
        ]);
    }
}
