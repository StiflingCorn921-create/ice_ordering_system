<?php

class UserController
{
    /** Admin: change own password. */
    public static function changePassword(): void
    {
        require_login();
        verify_csrf();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = User::findByUsername(current_user()['username']);

        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('danger', 'Current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash('danger', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash('danger', 'New passwords do not match.');
        } else {
            User::updatePassword((int) $user['id'], $new);
            flash('success', 'Password changed.');
        }
        redirect('admin/index.php');
    }

    /** Admin: mark a customer as Retail or Reseller (controls which price they get). */
    public static function setCustomerType(): void
    {
        require_login();
        verify_csrf();

        $id   = (int) ($_POST['customer_id'] ?? 0);
        $type = $_POST['customer_type'] ?? '';

        if (!Customer::find($id) || !in_array($type, ['Retail', 'Reseller'], true)) {
            flash('danger', 'Invalid customer or type.');
        } else {
            Customer::updateType($id, $type);
            flash('success', "Customer updated to $type.");
        }
        redirect('admin/customers.php');
    }
}

