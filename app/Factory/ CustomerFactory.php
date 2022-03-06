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

namespace App\Factory;

use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerFactory
{
    public static function create(int $company_id, int $user_id) :Customer
    {
        $customer = new Customer;
        $customer->company_id = $company_id;
        $customer->user_id = $user_id;
        $customer->name = '';
        $customer->phone = '';
        $customer->email = '';
        $customer->is_deleted = 0;

        return $customer;
    }
}
