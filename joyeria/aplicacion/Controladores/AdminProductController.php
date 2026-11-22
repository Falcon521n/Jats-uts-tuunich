<?php

declare(strict_types=1);

namespace Aplicacion\Controladores;

use Aplicacion\Modelos\CategoryModel;
use Aplicacion\Modelos\InventoryMovementModel;
use Aplicacion\Modelos\ProductModel;
use DomainException;
use Throwable;

final class AdminProductController extends Controller
{
    public function index(): void
    {
        $admin = require_role('admin');
        $productsModel = new ProductModel($this->pdo);
        $categoriesModel = new CategoryModel($this->pdo);
        $errors = [];
        $product = null;
        $editId = positive_int($_GET['editar'] ?? null);
        if ($editId !== null) {
            $product = $productsModel->findForAdministration($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = post_string('action', 20);
            if ($action === 'toggle') {
                $id = positive_int($_POST['product_id'] ?? null);
                if ($id === null || $productsModel->findForAdministration($id) === null) {
                    $errors[] = 'Producto no válido.';
                } else {
                    $productsModel->toggleActive($id);
                    flash('success', 'Se actualizó el estado del producto.');
                    redirect('admin_productos.php');
                }
            } elseif ($action === 'save') {
                $id = positive_int($_POST['product_id'] ?? null);
                $name = post_string('name', 160);
                $description = post_string('description', 5000);
                $category = post_string('category', 50);
                $pieceType = post_string('piece_type', 80);
                $material = post_string('material', 80);
                $price = valid_money(post_string('price', 30));
                $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
                $artisan = post_string('artisan_name', 120);
                $categoryId = $categoriesModel->findIdByName($category);

                if ($name === '' || $description === '' || $pieceType === '' || $material === '') {
                    $errors[] = 'Completa el nombre, la descripción, el tipo de pieza y el material.';
                }
                if ($categoryId === null) {
                    $errors[] = 'Selecciona una categoría válida.';
                }
                if ($price === null) {
                    $errors[] = 'Escribe un precio válido y no negativo.';
                }
                if ($stock === false || $stock < 0 || $stock > 1000000) {
                    $errors[] = 'La existencia debe ser un número entre 0 y 1,000,000.';
                }

                $imagePath = null;
                $upload = $_FILES['image'] ?? null;
                if ($errors === [] && is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $imagePath = $this->storeImage($upload, $errors);
                }

                if ($errors === []) {
                    $this->pdo->beginTransaction();
                    try {
                        $values = [
                            'category_id' => $categoryId,
                            'name' => $name,
                            'description' => $description,
                            'piece_type' => $pieceType,
                            'material' => $material,
                            'price' => $price,
                            'stock' => $stock,
                            'artisan_name' => $artisan !== '' ? $artisan : null,
                            'image_url' => $imagePath,
                        ];
                        if ($id !== null) {
                            $current = $productsModel->findForAdministration($id, true);
                            if ($current === null) {
                                throw new DomainException('El producto ya no existe.');
                            }
                            $values['image_url'] ??= $current['image_url'];
                            $productsModel->update($id, $values);
                            $delta = (int) $stock - (int) $current['stock'];
                            if ($delta !== 0) {
                                (new InventoryMovementModel($this->pdo))->record(
                                    $id,
                                    (int) $admin['id'],
                                    $delta,
                                    'adjustment',
                                    'Ajuste desde administración'
                                );
                            }
                        } else {
                            $id = $productsModel->insert($values);
                            if ((int) $stock > 0) {
                                (new InventoryMovementModel($this->pdo))->record(
                                    $id,
                                    (int) $admin['id'],
                                    (int) $stock,
                                    'purchase',
                                    'Existencia inicial'
                                );
                            }
                        }
                        $this->pdo->commit();
                        flash('success', 'Producto guardado.');
                        redirect('admin_productos.php');
                    } catch (DomainException $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        if ($imagePath !== null) {
                            @unlink(dirname(__DIR__, 2) . '/publico/' . $imagePath);
                        }
                        $errors[] = $exception->getMessage();
                    } catch (Throwable $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        if ($imagePath !== null) {
                            @unlink(dirname(__DIR__, 2) . '/publico/' . $imagePath);
                        }
                        throw $exception;
                    }
                } elseif ($imagePath !== null) {
                    @unlink(dirname(__DIR__, 2) . '/publico/' . $imagePath);
                }
            } else {
                $errors[] = 'La acción solicitada no es válida.';
            }
        }

        $this->render('administracion/products', [
            'pageTitle' => 'Administrar productos | Tuunich',
            'errors' => $errors,
            'product' => $product,
            'categories' => $categoriesModel->all(),
            'products' => $productsModel->allForAdministration(),
        ]);
    }

    private function storeImage(array $upload, array &$errors): ?string
    {
        if (
            ($upload['error'] ?? null) !== UPLOAD_ERR_OK
            || !is_int($upload['size'] ?? null)
            || $upload['size'] > 5 * 1024 * 1024
            || !is_string($upload['tmp_name'] ?? null)
            || !is_uploaded_file($upload['tmp_name'])
        ) {
            $errors[] = 'La imagen no se pudo subir o supera 5 MB.';
            return null;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            $errors[] = 'La imagen debe ser JPG, PNG o WebP.';
            return null;
        }

        $directory = dirname(__DIR__, 2) . '/publico/cargas';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            $errors[] = 'No se pudo preparar el directorio de imágenes.';
            return null;
        }
        $path = 'cargas/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($upload['tmp_name'], dirname(__DIR__, 2) . '/publico/' . $path)) {
            $errors[] = 'No se pudo guardar la imagen.';
            return null;
        }

        return $path;
    }
}
