<?php

namespace App\Modules\Email\Policies;

use App\Models\Admin;
use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;

class EmailPolicy
{
    /**
     * Determine if the user can view the email account.
     */
    public function viewAccount(Admin $user, EmailAccount $account): bool
    {
        return $user->canAccessEmailAccount($account);
    }

    /**
     * Determine if the user can manage the email account (Owner/Manager).
     */
    public function manageAccount(Admin $user, EmailAccount $account): bool
    {
        $role = $user->getEmailAccountRole($account);
        return in_array($role, ['owner', 'manager']);
    }

    /**
     * Determine if the user can delete an account (Owner only).
     */
    public function deleteAccount(Admin $user, EmailAccount $account): bool
    {
        return $user->getEmailAccountRole($account) === 'owner';
    }

    /**
     * Determine if the user can view a specific email.
     */
    public function view(Admin $user, Email $email): bool
    {
        return $user->canAccessEmailAccount($email->email_account_id);
    }

    /**
     * Determine if the user can reply/send emails.
     */
    public function send(Admin $user, EmailAccount $account): bool
    {
        $role = $user->getEmailAccountRole($account);
        return in_array($role, ['owner', 'manager', 'agent']);
    }
}
