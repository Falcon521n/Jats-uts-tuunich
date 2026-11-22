<?php

declare(strict_types=1);

namespace Aplicacion\Controladores;

use Aplicacion\Modelos\CartModel;
use Aplicacion\Modelos\CreditModel;
use Aplicacion\Modelos\CustomerModel;
use Aplicacion\Modelos\InventoryMovementModel;
use Aplicacion\Modelos\PaymentMethodModel;
use Aplicacion\Modelos\ProductModel;
use Aplicacion\Modelos\SaleModel;
use Aplicacion\Servicios\CartService;
use Aplicacion\Servicios\SaleService;
use DomainException;

final class CartController extends Controller
{
    public function index(): void
    {
        $user = require_role('customer');
        $customerModel = new CustomerModel($this->pdo);
        $customerId = $customerModel->findIdByUserId((int) $user['id']);
        if ($customerId === null) {
            http_response_code(403);
            exit('La cuenta de cliente no está configurada.');
        }

        $cartModel = new CartModel($this->pdo);
        $cartId = $cartModel->findOrCreateActive($customerId);
        $_SESSION['cart_id'] = $cartId;
        $profile = $customerModel->profileForUser((int) $user['id']);
        if ($profile === null) {
            http_response_code(403);
            exit('La cuenta de cliente no está configurada.');
        }
        $cardholderName = trim($profile['name'] . ' ' . $profile['last_name']);
        $productModel = new ProductModel($this->pdo);
        $cartService = new CartService($cartModel, $productModel);
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = post_string('action', 20);
            try {
                if ($action === 'add') {
                    $productId = positive_int($_POST['product_id'] ?? null);
                    $quantity = positive_int($_POST['quantity'] ?? null, 10000);
                    if ($productId === null || $quantity === null) {
                        throw new DomainException('Selecciona una cantidad válida.');
                    }
                    $cartService->add($cartId, $productId, $quantity);
                    flash('success', 'La pieza se agregó a tu carrito.');
                } elseif ($action === 'update') {
                    $quantities = $_POST['quantities'] ?? null;
                    if (!is_array($quantities)) {
                        throw new DomainException('No se pudieron actualizar las cantidades.');
                    }
                    $cartService->update($cartId, $quantities);
                    flash('success', 'Carrito actualizado.');
                } elseif ($action === 'remove') {
                    $productId = positive_int($_POST['product_id'] ?? null);
                    if ($productId === null) {
                        throw new DomainException('Selecciona un producto válido.');
                    }
                    $cartService->remove($cartId, $productId);
                    flash('success', 'Producto retirado del carrito.');
                } elseif ($action === 'checkout') {
                    if (($_POST['confirm'] ?? '') !== 'yes') {
                        throw new DomainException('Confirma que deseas finalizar la compra.');
                    }
                    $paymentMethod = post_string('payment_method', 20);
                    if ($paymentMethod !== 'debit') {
                        throw new DomainException('Solo aceptamos pagos con tarjeta.');
                    }
                    $submittedCardholder = preg_replace(
                        '/\s+/',
                        ' ',
                        strtolower(post_string('cardholder', 241))
                    );
                    $registeredCardholder = preg_replace('/\s+/', ' ', strtolower($cardholderName));
                    if (
                        post_string('card_number', 16) !== '1234567891234567'
                        || post_string('card_expiration', 4) !== '1234'
                        || post_string('card_security_code', 3) !== '123'
                        || $submittedCardholder !== $registeredCardholder
                    ) {
                        throw new DomainException(
                            'Tarjeta rechazada. Verifica los datos de demostración y que el titular coincida con tu nombre registrado.'
                        );
                    }
                    $saleId = (new SaleService(
                        $this->pdo,
                        $productModel,
                        $customerModel,
                        new PaymentMethodModel($this->pdo),
                        new SaleModel($this->pdo),
                        new CreditModel($this->pdo),
                        new InventoryMovementModel($this->pdo),
                        $cartModel
                    ))->checkoutCart(
                        $cartId,
                        $customerId,
                        (int) $user['id'],
                        $paymentMethod
                    );
                    unset($_SESSION['cart_id']);
                    flash('success', 'Compra registrada. Número de venta: #' . $saleId);
                    redirect('cuenta.php');
                } else {
                    throw new DomainException('La acción solicitada no es válida.');
                }
                redirect('carrito.php');
            } catch (DomainException $exception) {
                $error = $exception->getMessage();
            }
        }

        $lines = $cartService->lines($cartId);
        $total = array_sum(array_map(
            static fn(array $line): float => (float) $line['subtotal'],
            $lines
        ));

        $this->render('carrito/index', [
            'pageTitle' => 'Carrito | Tuunich',
            'error' => $error,
            'lines' => $lines,
            'total' => $total,
            'cardholderName' => $cardholderName,
        ]);
    }
}
