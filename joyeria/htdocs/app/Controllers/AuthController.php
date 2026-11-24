<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\CartModel;
use App\Models\CreditModel;
use App\Models\CustomerModel;
use App\Models\UserModel;
use App\Services\AuthService;
use PDOException;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (current_user() !== null) {
            redirect('index.php');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $identifier = strtolower(post_string('identifier', 190));
            $password = $_POST['password'] ?? '';
            if (!is_string($password)) {
                $password = '';
            }

            $service = $this->authService();
            $account = $service->authenticate($identifier, $password);
            if ($account !== null) {
                $customerId = null;
                if ($account['role'] === 'customer') {
                    $customerId = (new CustomerModel($this->pdo))->findIdByUserId($account['id']);
                    if ($customerId === null) {
                        $errors[] = 'La cuenta de cliente no está configurada.';
                    }
                }

                if ($errors === []) {
                    session_regenerate_id(true);
                    unset($_SESSION['csrf_token']);
                    $_SESSION['user'] = $account;
                    if ($customerId !== null) {
                        $_SESSION['cart_id'] = (new CartModel($this->pdo))->findOrCreateActive($customerId);
                    }
                    redirect('index.php');
                }
            } else {
                $errors[] = 'El usuario o la contraseña no son correctos.';
            }
        }

        $this->render('auth/login', [
            'pageTitle' => 'Iniciar sesión | Tuunich',
            'errors' => $errors,
            'identifier' => post_string('identifier', 190),
        ]);
    }

    public function register(): void
    {
        if (current_user() !== null) {
            redirect('index.php');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $data = [
                'name' => post_string('name', 120),
                'last_name' => post_string('last_name', 120),
                'email' => strtolower(post_string('email', 190)),
                'password' => $_POST['password'] ?? '',
                'phone' => post_string('phone', 30),
                'address' => post_string('address', 500),
            ];
            if ($data['name'] === '' || $data['last_name'] === '') {
                $errors[] = 'Escribe tu nombre y apellidos.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Escribe un correo válido.';
            }
            if (!is_string($data['password']) || strlen($data['password']) < 10 || strlen($data['password']) > 200) {
                $errors[] = 'La contraseña debe tener entre 10 y 200 caracteres.';
            }
            if (($_POST['terms'] ?? '') !== 'accepted') {
                $errors[] = 'Debes aceptar los términos para crear la cuenta.';
            }

            if ($errors === []) {
                try {
                    $result = $this->authService()->registerCustomer($data);
                    session_regenerate_id(true);
                    unset($_SESSION['csrf_token']);
                    $_SESSION['user'] = $result['user'];
                    $_SESSION['cart_id'] = $result['cart_id'];
                    redirect('account.php');
                } catch (PDOException $exception) {
                    if ($exception->getCode() === '23000') {
                        $errors[] = 'Ya existe una cuenta con ese correo.';
                    } else {
                        throw $exception;
                    }
                }
            }
        }

        $this->render('auth/register', [
            'pageTitle' => 'Crear cuenta | Tuunich',
            'errors' => $errors,
            'form' => [
                'name' => post_string('name', 120),
                'last_name' => post_string('last_name', 120),
                'email' => post_string('email', 190),
                'phone' => post_string('phone', 30),
                'address' => post_string('address', 500),
            ],
        ]);
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método no permitido.');
        }
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, [
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        redirect('login.php');
    }

    private function authService(): AuthService
    {
        return new AuthService(
            $this->pdo,
            new UserModel($this->pdo),
            new AccountModel($this->pdo),
            new CustomerModel($this->pdo),
            new CreditModel($this->pdo),
            new CartModel($this->pdo)
        );
    }
}
