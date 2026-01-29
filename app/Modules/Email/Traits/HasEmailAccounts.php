<?php

namespace App\Modules\Email\Traits;

use App\Modules\Email\Models\EmailAccount;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasEmailAccounts
{
    /**
     * Get all email accounts assigned to this admin.
     */
    public function emailAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            EmailAccount::class,
            'email_account_users',
            'user_id',
            'email_account_id'
        )->withPivot('role', 'company_id')
         ->withTimestamps();
    }

    /**
     * Check if admin has access to a specific email account.
     */
    public function canAccessEmailAccount(int|EmailAccount $account): bool
    {
        $id = $account instanceof EmailAccount ? $account->id : $account;
        return $this->emailAccounts()->where('email_account_id', $id)->exists();
    }

    /**
     * Get the role for a specific email account.
     */
    public function getEmailAccountRole(int|EmailAccount $account): ?string
    {
        $id = $account instanceof EmailAccount ? $account->id : $account;
        return $this->emailAccounts()
            ->where('email_account_id', $id)
            ->value('role');
    }
}
