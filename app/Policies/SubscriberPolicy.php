<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Subscriber;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Subscriber');
    }

    public function view(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('View:Subscriber');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Subscriber');
    }

    public function update(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('Update:Subscriber');
    }

    public function delete(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('Delete:Subscriber');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Subscriber');
    }

    public function restore(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('Restore:Subscriber');
    }

    public function forceDelete(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('ForceDelete:Subscriber');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Subscriber');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Subscriber');
    }

    public function replicate(AuthUser $authUser, Subscriber $subscriber): bool
    {
        return $authUser->can('Replicate:Subscriber');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Subscriber');
    }

}