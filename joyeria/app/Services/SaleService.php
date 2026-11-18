<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartModel;
use App\Models\CreditModel;
use App\Models\CustomerModel;
use App\Models\InventoryMovementModel;
use App\Models\PaymentMethodModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\SellerModel;
use DomainException;
use PDO;
use Throwable;

final class SaleService
{
    public function __construct(
        private PDO $pdo,
        private ProductModel $products,
        private CustomerModel $customers,
        private SellerModel $sellers,
        private PaymentMethodModel $paymentMethods,
        private SaleModel $sales,
        private CreditModel $credits,
        private InventoryMovementModel $inventory,
        private CartModel $carts
    ) {
    }

    public function checkoutCart(int $cartId, int $customerId, int $actorUserId, string $paymentMethod): int
    {
        return $this->complete([], $customerId, null, $actorUserId, $paymentMethod, $cartId);
    }

    public function registerSellerSale(
        array $items,
        int $customerId,
        int $sellerId,
        int $actorUserId,
        string $paymentMethod
    ): int {
        return $this->complete($items, $customerId, $sellerId, $actorUserId, $paymentMethod, null);
    }

    private function complete(
        array $items,
        int $customerId,
        ?int $sellerId,
        int $actorUserId,
        string $paymentMethod,
        ?int $cartId
    ): int {
        $methodId = $this->paymentMethods->idFor($paymentMethod);
        if ($methodId === null) {
            throw new DomainException('Selecciona un método de pago válido.');
        }

        $this->pdo->beginTransaction();
        try {
            if ($cartId !== null) {
                if (!$this->carts->lockOwnedActive($cartId, $customerId)) {
                    throw new DomainException('El carrito ya no está disponible.');
                }
                $items = $this->carts->items($cartId);
            }
            if ($items === []) {
                throw new DomainException('Agrega al menos un producto antes de comprar.');
            }

            $quantities = [];
            foreach ($items as $productId => $quantity) {
                $id = filter_var($productId, FILTER_VALIDATE_INT);
                $count = filter_var($quantity, FILTER_VALIDATE_INT);
                if ($id === false || $id < 1 || $count === false || $count < 1 || $count > 10000) {
                    throw new DomainException('El carrito contiene cantidades no válidas.');
                }
                $quantities[(int) $id] = (int) $count;
            }
            ksort($quantities, SORT_NUMERIC);

            $saleItems = [];
            $totalCents = 0;
            foreach ($quantities as $productId => $quantity) {
                $product = $this->products->findAvailableForUpdate($productId);
                if (!$product || (int) $product['stock'] < $quantity) {
                    throw new DomainException('Una pieza dejó de estar disponible en la cantidad solicitada.');
                }
                $unitCents = $this->moneyToCents((string) $product['price']);
                $subtotalCents = $unitCents * $quantity;
                $totalCents += $subtotalCents;
                if ($totalCents > 9999999999) {
                    throw new DomainException('El total supera el máximo permitido.');
                }
                $saleItems[] = [
                    'id' => $productId,
                    'name' => $product['name'],
                    'unit_price' => number_format($unitCents / 100, 2, '.', ''),
                    'quantity' => $quantity,
                    'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                    'subtotal_cents' => $subtotalCents,
                ];
            }

            if (!$this->customers->lockActive($customerId)) {
                throw new DomainException('El cliente no existe o está inactivo.');
            }
            if ($sellerId !== null && !$this->sellers->lockActive($sellerId)) {
                throw new DomainException('El vendedor no existe o está inactivo.');
            }

            if ($paymentMethod === 'credit') {
                $credit = $this->credits->forCustomer($customerId, true);
                if (!$credit || $credit['state'] !== 'active') {
                    throw new DomainException('El cliente no tiene crédito activo.');
                }
                $limitCents = $this->moneyToCents((string) $credit['credit_limit']);
                $balanceCents = $this->moneyToCents((string) $credit['balance']);
                if ($balanceCents + $totalCents > $limitCents) {
                    throw new DomainException('La compra excede el crédito disponible del cliente.');
                }
            }

            $saleId = $this->sales->create(
                $customerId,
                $sellerId,
                $methodId,
                number_format($totalCents / 100, 2, '.', '')
            );
            foreach ($saleItems as $item) {
                $this->sales->addItem($saleId, $item);
                if (!$this->products->decrementStock($item['id'], $item['quantity'])) {
                    throw new DomainException('No hay existencia suficiente para completar la venta.');
                }
                $this->inventory->record(
                    $item['id'],
                    $actorUserId,
                    -$item['quantity'],
                    'sale',
                    'Venta #' . $saleId
                );
            }
            if ($paymentMethod === 'credit') {
                $this->credits->increaseBalance(
                    $customerId,
                    number_format($totalCents / 100, 2, '.', '')
                );
            }
            if ($cartId !== null) {
                $this->carts->clear($cartId);
                $this->carts->finalize($cartId);
            }

            $this->pdo->commit();

            return $saleId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function moneyToCents(string $value): int
    {
        $parts = explode('.', $value, 2);

        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }
}
