<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VisitorLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitorLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VisitorLog');
    }

    public function view(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('View:VisitorLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VisitorLog');
    }

    public function update(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('Update:VisitorLog');
    }

    public function delete(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('Delete:VisitorLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VisitorLog');
    }

    public function restore(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('Restore:VisitorLog');
    }

    public function forceDelete(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('ForceDelete:VisitorLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VisitorLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VisitorLog');
    }

    public function replicate(AuthUser $authUser, VisitorLog $visitorLog): bool
    {
        return $authUser->can('Replicate:VisitorLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VisitorLog');
    }

}