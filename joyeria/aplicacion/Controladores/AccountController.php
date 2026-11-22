<?php

declare(strict_types=1);

namespace Aplicacion\Controladores;

use Aplicacion\Modelos\CustomerModel;
use Aplicacion\Modelos\SaleModel;

final class AccountController extends Controller
{
    public function index(): void
    {
        $user = require_role('customer');
        $customers = new CustomerModel($this->pdo);
        $profile = $customers->profileForUser((int) $user['id']);
        if ($profile === null) {
            http_response_code(403);
            exit('La cuenta de cliente no está configurada.');
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $name = post_string('name', 120);
            $lastName = post_string('last_name', 120);
            if ($name === '' || $lastName === '') {
                $error = 'Nombre y apellidos son obligatorios.';
            } else {
                $customers->updateProfile(
                    (int) $user['id'],
                    $name,
                    $lastName,
                    post_string('phone', 30) ?: null,
                    post_string('address', 500) ?: null
                );
                $_SESSION['user']['name'] = $name;
                flash('success', 'Tus datos se actualizaron.');
                redirect('cuenta.php');
            }
            $profile['name'] = $name;
            $profile['last_name'] = $lastName;
            $profile['phone'] = post_string('phone', 30);
            $profile['address'] = post_string('address', 500);
        }

        $period = $_GET['periodo'] ?? 'all';
        if (!is_string($period) || !in_array($period, ['all', '30', '365'], true)) {
            $period = 'all';
        }
        $customerId = (int) $profile['customer_id'];
        $history = (new SaleModel($this->pdo))->customerHistory($customerId, $period);

        $this->render('cuenta/index', [
            'pageTitle' => 'Mi cuenta | Tuunich',
            'profile' => $profile,
            'period' => $period,
            'history' => $history,
            'error' => $error,
        ]);
    }
}
