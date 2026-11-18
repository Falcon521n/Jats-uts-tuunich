<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\CreditModel;
use App\Models\CustomerModel;
use App\Models\SellerModel;
use App\Models\UserModel;
use DomainException;
use PDOException;
use Throwable;

final class AdminUserController extends Controller
{
    public function index(): void
    {
        $admin = require_role('admin');
        $usersModel = new UserModel($this->pdo);
        $customersModel = new CustomerModel($this->pdo);
        $sellersModel = new SellerModel($this->pdo);
        $creditsModel = new CreditModel($this->pdo);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = post_string('action', 20);
            if ($action === 'save') {
                $id = positive_int($_POST['user_id'] ?? null);
                $name = post_string('name', 120);
                $lastName = post_string('last_name', 120);
                $email = strtolower(post_string('email', 190));
                $phone = post_string('phone', 30);
                $role = post_string('role', 20);
                $active = $_POST['is_active'] ?? '';
                $password = $_POST['password'] ?? '';
                $creditLimit = valid_money(post_string('credit_limit', 30));

                if ($name === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Escribe nombre, apellidos y un correo válido.';
                }
                if (!in_array($role, ['customer', 'seller', 'admin'], true) || !in_array($active, ['0', '1'], true)) {
                    $errors[] = 'Selecciona un rol y estado válidos.';
                }
                if ($creditLimit === null) {
                    $errors[] = 'El límite de crédito debe ser un importe válido.';
                }
                if ($role !== 'customer' && $creditLimit !== '0.00') {
                    $errors[] = 'Solo los clientes pueden tener límite de crédito.';
                }
                if ($id === null && (!is_string($password) || strlen($password) < 10 || strlen($password) > 200)) {
                    $errors[] = 'La contraseña inicial debe tener entre 10 y 200 caracteres.';
                }
                if ($id !== null && $password !== '' && (!is_string($password) || strlen($password) < 10 || strlen($password) > 200)) {
                    $errors[] = 'La contraseña debe tener entre 10 y 200 caracteres.';
                }
                if ($id === (int) $admin['id'] && ($role !== 'admin' || $active !== '1')) {
                    $errors[] = 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.';
                }

                $current = $id === null ? null : $usersModel->findForAdministration($id);
                if ($id !== null && $current === null) {
                    $errors[] = 'El usuario ya no existe.';
                } elseif ($current !== null && $role !== $current['role']) {
                    $errors[] = 'No es posible cambiar el rol de una cuenta existente; crea una cuenta nueva o desactívala.';
                } elseif ($current !== null && $current['role'] === 'customer'
                    && money_to_cents((string) $current['balance']) > 0 && $active !== '1') {
                    $errors[] = 'No puedes desactivar a un cliente con saldo de crédito pendiente.';
                }

                if ($errors === []) {
                    try {
                        $this->pdo->beginTransaction();
                        $userData = [
                            'name' => $name,
                            'last_name' => $lastName,
                            'email' => $email,
                            'role' => $role,
                            'is_active' => (int) $active,
                        ];
                        if ($password !== '') {
                            $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                        if ($id !== null) {
                            $usersModel->update($id, $userData);
                        } else {
                            $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                            $id = $usersModel->create($userData);
                            if ($role === 'customer') {
                                $customerId = $customersModel->createForUser(
                                    $id,
                                    $phone !== '' ? $phone : null,
                                    null
                                );
                                $creditsModel->createForCustomer($customerId);
                            } elseif ($role === 'seller') {
                                $sellersModel->createForUser($id, $phone !== '' ? $phone : null);
                            }
                        }

                        if ($role === 'customer') {
                            $customersModel->updateForUser(
                                $id,
                                $phone !== '' ? $phone : null,
                                $current['address'] ?? null
                            );
                            $customerId = $customersModel->findIdByUserId($id);
                            if ($customerId !== null) {
                                $creditsModel->assignLimit($customerId, $creditLimit);
                            }
                        } elseif ($role === 'seller') {
                            $sellersModel->updateForUser($id, $phone !== '' ? $phone : null, $active === '1' ? 'active' : 'inactive');
                        }

                        $this->pdo->commit();
                        flash('success', 'Usuario guardado.');
                        redirect('admin_usuarios.php');
                    } catch (PDOException $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        if ($exception->getCode() === '23000') {
                            $errors[] = 'Ese correo ya está registrado.';
                        } else {
                            throw $exception;
                        }
                    } catch (Throwable $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        throw $exception;
                    }
                }
            } elseif ($action === 'payment') {
                $customerId = positive_int($_POST['customer_id'] ?? null);
                $amount = valid_money(post_string('amount', 30));
                $note = post_string('note', 255);
                if ($customerId === null || $amount === null || money_to_cents($amount) <= 0) {
                    $errors[] = 'Selecciona un cliente y un abono mayor que cero.';
                } else {
                    try {
                        $this->pdo->beginTransaction();
                        $credit = $creditsModel->forCustomer($customerId, true);
                        if ($credit === null || money_to_cents($amount) > money_to_cents((string) $credit['balance'])) {
                            throw new DomainException('El abono no puede superar el saldo pendiente del cliente.');
                        }
                        $creditsModel->applyPayment(
                            $customerId,
                            $amount,
                            (int) $admin['id'],
                            $note !== '' ? $note : null
                        );
                        $this->pdo->commit();
                        flash('success', 'Abono registrado.');
                        redirect('admin_usuarios.php');
                    } catch (DomainException $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $errors[] = $exception->getMessage();
                    } catch (Throwable $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        throw $exception;
                    }
                }
            } else {
                $errors[] = 'La acción solicitada no es válida.';
            }
        }

        $editId = positive_int($_GET['editar'] ?? null);
        $editUser = $editId === null ? null : $usersModel->findForAdministration($editId);
        $users = $usersModel->listForAdministration();
        $customers = array_values(array_filter(
            $users,
            static fn(array $item): bool => $item['role'] === 'customer'
                && ((int) $item['is_active'] === 1 || money_to_cents((string) $item['balance']) > 0)
        ));

        $this->render('admin/users', [
            'pageTitle' => 'Administrar usuarios | Tuunich',
            'admin' => $admin,
            'errors' => $errors,
            'editUser' => $editUser,
            'users' => $users,
            'customers' => $customers,
        ]);
    }
}
