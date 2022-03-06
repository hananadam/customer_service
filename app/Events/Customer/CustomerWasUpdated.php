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

namespace App\Events\Customer;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Queue\SerializesModels;

/**
 * Class CustomerWasUpdated.
 */
class CustomerWasUpdated
{
    use SerializesModels;

    /**
     * @var Customer
     */
    public $customer;

    public $company;

    public $event_vars;

    /**
     * Create a new event instance.
     *
     * @param Customer $customer
     * @param Company $company
     * @param array $event_vars
     */
    public function __construct(Customer $customer, Company $company, array $event_vars)
    {
        $this->customer = $customer;
        $this->company = $company;
        $this->event_vars = $event_vars;
    }
}
