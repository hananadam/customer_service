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

namespace App\Transformers;

use App\Models\Activity;
use App\Models\Customer;
use App\Utils\Traits\MakesHash;
use League\Fractal\Resource\Collection;
use stdClass;

/**
 * class CustomerTransformer.
 */
class CustomerTransformer extends EntityTransformer
{
    use MakesHash;

    protected $defaultIncludes = [
    ];

    /**
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * @param Customer $customer
     *
     * @return Collection
     */
  
    /**
     * @param Customer $customer
     *
     * @return array
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     */
    public function transform(Customer $customer)
    {
        return [
            'id' => $this->encodePrimaryKey($customer->id),
            'user_id' => $this->encodePrimaryKey($customer->user_id),
            'assigned_user_id' => $this->encodePrimaryKey($customer->assigned_user_id),
            'name' => $customer->name ?: '',
            'email' => $customer->email ?: '',
            'phone' => $customer->phone ?: '',
            'is_deleted' => (bool) $customer->is_deleted,
            'updated_at' => (int) $customer->updated_at,
            'archived_at' => (int) $customer->deleted_at,
            'created_at' => (int) $customer->created_at,
        ];
    }
}
