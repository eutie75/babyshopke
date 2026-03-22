<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Product.php';

function handleProductCreateSubmission(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('admin/product_add.php');
    }

    $validation = Product::validate($_POST);
    if (!$validation['valid']) {
        flash('error', implode(' ', $validation['errors']));
        redirect('admin/product_add.php');
    }

    try {
        Product::create($validation['data']);
    } catch (Throwable $e) {
        flash('error', 'Unable to create product. Please check the values and try again.');
        redirect('admin/product_add.php');
    }

    flash('success', 'Product created successfully.');
    redirect('admin/products.php');
}

function handleProductUpdateSubmission(int $productId): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('admin/product_edit.php?id=' . $productId);
    }

    $existing = Product::getById($productId);
    if (!$existing) {
        flash('error', 'Product not found.');
        redirect('admin/products.php');
    }

    $validation = Product::validate($_POST);
    if (!$validation['valid']) {
        flash('error', implode(' ', $validation['errors']));
        redirect('admin/product_edit.php?id=' . $productId);
    }

    try {
        Product::update($productId, $validation['data']);
    } catch (Throwable $e) {
        flash('error', 'Unable to update this product.');
        redirect('admin/product_edit.php?id=' . $productId);
    }

    flash('success', 'Product updated successfully.');
    redirect('admin/products.php');
}

function handleProductDeleteSubmission(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('admin/products.php');
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId <= 0) {
        flash('error', 'Invalid product.');
        redirect('admin/products.php');
    }

    try {
        Product::delete($productId);
        flash('success', 'Product deleted.');
    } catch (Throwable $e) {
        flash('error', 'Product cannot be deleted because it is linked to orders.');
    }

    redirect('admin/products.php');
}
