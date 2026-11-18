<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AccountModel;
use App\Models\CartModel;
use App\Models\CreditModel;
use App\Models\CustomerModel;
use App\Models\UserModel;
use PDO;
use Throwable;

final class AuthService
{
    public function __construct(
        private PDO $pdo,
        private UserModel $users,
        private AccountModel $accounts,
        private CustomerModel $customers,
        private CreditModel $credits,
        private CartModel $carts
    ) {
    }

    public function authenticate(string $email, string $password): ?array
    {
        $account = $this->users->findActiveByEmail($email);
        if (!$account || strlen($password) > 200 || !password_verify($password, $account['password_hash'])) {
            return null;
        }
        if (password_needs_rehash($account['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePasswordHash((int) $account['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        return [
            'id' => (int) $account['id'],
            'name' => $account['name'],
            'email' => $account['email'],
            'role' => $account['role'],
        ];
    }

    public function registerCustomer(array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            $userId = $this->accounts->createCustomerUser([
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);
            $customerId = $this->customers->createForUser(
                $userId,
                $data['phone'] !== '' ? $data['phone'] : null,
                $data['address'] !== '' ? $data['address'] : null
            );
            $this->credits->createForCustomer($customerId);
            $cartId = $this->carts->findOrCreateActive($customerId);
            $this->pdo->commit();

            return [
                'user' => ['id' => $userId, 'name' => $data['name'], 'email' => $data['email'], 'role' => 'customer'],
                'customer_id' => $customerId,
                'cart_id' => $cartId,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }
}
