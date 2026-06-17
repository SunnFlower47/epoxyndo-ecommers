<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AdminActivityLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminActivityLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdminActivityLog');
    }

    public function view(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('View:AdminActivityLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdminActivityLog');
    }

    public function update(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('Update:AdminActivityLog');
    }

    public function delete(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('Delete:AdminActivityLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AdminActivityLog');
    }

    public function restore(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('Restore:AdminActivityLog');
    }

    public function forceDelete(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('ForceDelete:AdminActivityLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdminActivityLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdminActivityLog');
    }

    public function replicate(AuthUser $authUser, AdminActivityLog $adminActivityLog): bool
    {
        return $authUser->can('Replicate:AdminActivityLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdminActivityLog');
    }

}