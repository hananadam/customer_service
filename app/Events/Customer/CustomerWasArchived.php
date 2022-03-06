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
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class CustomerWasArchived.
 */
class CustomerWasArchived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
