<?php
/**
 * NinjaPMS (https://ninjapms.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. NinjaPMS LLC (https://ninjapms.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Policies;

use App\Models\User;

/**
 * Class CustomerPolicy.
 */
class CustomerPolicy extends EntityPolicy
{
    /**
     *  Checks if the user has create permissions.
     *
     * @param  User $user
     * @return bool
     */
    public function create(User $user) : bool
    {
        return $user->isAdmin() || $user->hasPermission('create_customer') || $user->hasPermission('create_all');
    }
}
