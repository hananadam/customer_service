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

namespace App\Observers;

use App\Jobs\Util\WebhookHandler;
use App\Models\Customer;
use App\Models\Webhook;

class ClientObserver
{
    /**
     * Handle the customer "created" event.
     *
     * @param Customer $customer
     * @return void
     */
    public function created(Customer $customer)
    {
        $subscriptions = Webhook::where('company_id', $customer->company->id)
                                    ->where('event_id', Webhook::EVENT_CREATE_CLIENT)
                                    ->exists();

        if ($subscriptions) {
            WebhookHandler::dispatch(Webhook::EVENT_CREATE_CLIENT, $customer, $customer->company);
        }
    }

    /**
     * Handle the customer "updated" event.
     *
     * @param Customer $customer
     * @return void
     */
    public function updated(Customer $customer)
    {
        $subscriptions = Webhook::where('company_id', $customer->company->id)
                                    ->where('event_id', Webhook::EVENT_UPDATE_CLIENT)
                                    ->exists();

        if ($subscriptions) {
            WebhookHandler::dispatch(Webhook::EVENT_UPDATE_CLIENT, $customer, $customer->company);
        }
    }

    /**
     * Handle the customer "deleted" event.
     *
     * @param Customer $customer
     * @return void
     */
    public function deleted(Customer $customer)
    {
        $subscriptions = Webhook::where('company_id', $customer->company->id)
                                    ->where('event_id', Webhook::EVENT_DELETE_CLIENT)
                                    ->exists();

        if ($subscriptions) {
            WebhookHandler::dispatch(Webhook::EVENT_DELETE_CLIENT, $customer, $customer->company);
        }
    }

    /**
     * Handle the customer "restored" event.
     *
     * @param Customer $customer
     * @return void
     */
    public function restored(Customer $client)
    {
        //
    }

    /**
     * Handle the client "force deleted" event.
     *
     * @param Customer $client
     * @return void
     */
    public function forceDeleted(Customer $client)
    {
        //
    }
}
