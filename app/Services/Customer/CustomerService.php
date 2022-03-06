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

namespace App\Services\Customer;

use App\Models\Customer;
use App\Utils\Number;

class CustomerService
{
    private $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

  

    public function save() :Customer
    {
        $this->customer->save();

        return $this->customer;
    }
}
