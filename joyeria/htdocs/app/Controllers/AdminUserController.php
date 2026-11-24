<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\UserModel;
use PDOException;
use Throwable;

final class AdminUserController extends Controller
{
    public function index(): void
    {
        $admin = require_role($this->pdo, 'admin');
        $usersModel = new UserModel($this->pdo);
        $customersModel = new CustomerModel($this->pdo);
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
                $role = 'customer';
                $active = $_POST['is_active'] ?? '';
                $password = $_POST['password'] ?? '';

                if ($name === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Escribe nombre, apellidos y un correo válido.';
                }
                if (!in_array($active, ['0', '1'], true)) {
                    $errors[] = 'Selecciona un estado válido.';
                }
                if ($id === null && (!is_string($password) || strlen($password) < 10 || strlen($password) > 200)) {
                    $errors[] = 'La contraseña inicial debe tener entre 10 y 200 caracteres.';
                }
                if ($id !== null && $password !== '' && (!is_string($password) || strlen($password) < 10 || strlen($password) > 200)) {
                    $errors[] = 'La contraseña debe tener entre 10 y 200 caracteres.';
                }
                $current = $id === null ? null : $usersModel->findForAdministration($id);
                if ($id !== null && $current === null) {
                    $errors[] = 'El usuario ya no existe.';
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
                            $customersModel->createForUser(
                                $id,
                                $phone !== '' ? $phone : null,
                                null
                            );
                        }

                        $customersModel->updateForUser(
                            $id,
                            $phone !== '' ? $phone : null,
                            $current['address'] ?? null
                        );

                        $this->pdo->commit();
                        flash('success', 'Usuario guardado.');
                        redirect('admin_users.php');
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
            } else {
                $errors[] = 'La acción solicitada no es válida.';
            }
        }

        $editId = positive_int($_GET['edit'] ?? null);
        $editUser = $editId === null ? null : $usersModel->findForAdministration($editId);
        $users = $usersModel->listForAdministration();

        $this->render('admin/users', [
            'pageTitle' => 'Administrar usuarios | Tuunich',
            'admin' => $admin,
            'errors' => $errors,
            'editUser' => $editUser,
            'users' => $users,
        ]);
    }
}
