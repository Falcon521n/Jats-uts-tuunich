<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\CreditModel;
use App\Models\CustomerModel;
use App\Models\InventoryMovementModel;
use App\Models\PaymentMethodModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\SellerModel;
use App\Services\SaleService;
use DomainException;

final class SellerController extends Controller
{
    public function index(): void
    {
        $user = require_role('seller');
        $sellerId = (new SellerModel($this->pdo))->findIdByUserId((int) $user['id']);
        if ($sellerId === null) {
            http_response_code(403);
            exit('La cuenta de vendedor está inactiva.');
        }
        $customersModel = new CustomerModel($this->pdo);
        $productsModel = new ProductModel($this->pdo);
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $customerId = positive_int($_POST['customer_id'] ?? null);
            $productId = positive_int($_POST['product_id'] ?? null);
            $quantity = positive_int($_POST['quantity'] ?? null, 10000);
            if ($customerId === null || $productId === null || $quantity === null) {
                $error = 'Completa cliente, producto y cantidad con valores válidos.';
            } else {
                try {
                    $saleId = (new SaleService(
                        $this->pdo,
                        $productsModel,
                        $customersModel,
                        new SellerModel($this->pdo),
                        new PaymentMethodModel($this->pdo),
                        new SaleModel($this->pdo),
                        new CreditModel($this->pdo),
                        new InventoryMovementModel($this->pdo),
                        new CartModel($this->pdo)
                    ))->registerSellerSale(
                        [$productId => $quantity],
                        $customerId,
                        $sellerId,
                        (int) $user['id'],
                        post_string('payment_method', 20)
                    );
                    flash('success', 'Venta registrada. Número de venta: #' . $saleId);
                    redirect('venta_vendedor.php');
                } catch (DomainException $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        $this->render('seller/index', [
            'pageTitle' => 'Ventas | Tuunich',
            'error' => $error,
            'customers' => $customersModel->activeCustomers(),
            'products' => $productsModel->activeForSale(),
            'sales' => (new SaleModel($this->pdo))->sellerHistory($sellerId),
        ]);
    }
}
