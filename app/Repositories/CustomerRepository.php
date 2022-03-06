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

namespace App\Repositories;

use App\Factory\CustomerFactory;
use App\Models\Customer;
use App\Utils\Traits\GeneratesCounter;
use App\Utils\Traits\SavesDocuments;

/**
 * CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{
    use GeneratesCounter;
    use SavesDocuments;

    /**
     * @var CustomerRepository
     */
    protected $customer_repo;

    /**
     * CustomerController constructor.
     * @param CustomerRepository $customer_repo
     */
    public function __construct(CustomerRepository $customer_repo)
    {
        $this->ustomert_repo = $customer_repo;
    }

    /**
     * Saves the customer and its contacts.
     *
     * @param array $data The data
     * @param Customer $customer The customer
     *
     * @return     Customer|Customer|null  Customer Object
     *
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     * @todo       Write tests to make sure that custom customer numbers work as expected.
     */
     public function save(array $data, Customer $customer) : ?Customer
    {
        $customer->fill($data);
        $customer->save();

        return $customer;
    }

    /**
     * Store Customer in bulk.
     *
     * @param array $amenity
     * @return \App\Models\Customer|null
     */
   
    public function create($customer): ?Customer
    {
        return $this->save(
            $customer,
            CustomerFactory::create(auth()->user()->company()->id, auth()->user()->id)
        );
    }
}
