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

namespace App\Filters;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * CustomerFilters.
 */
class CustomerFilters extends QueryFilters
{

    /**
     * Filter by name.
     *
     * @param string $name
     * @return Builder
     */
    public function name(string $name): Builder
    {
        return $this->builder->where('name', 'like', '%'.$name.'%');
    }

   
    public function number(string $number):Builder
    {
        return $this->builder->where('number', $number);
    }

    /**
     * Filter based on search text.
     *
     * @param string query filter
     * @return Builder
     * @deprecated
     */
    public function filter(string $filter = '') : Builder
    {
        //dd('filter');
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        return  $this->builder->where(function ($query) use ($filter) {
            $query->where('customers.name', 'like', '%'.$filter.'%')
                          ->orWhere('customers.phone', 'like', '%'.$filter.'%');
                         
        });
    }

    /**
     * Filters the list based on the status
     * archived, active, deleted.
     *
     * @param string filter
     * @return Builder
     */
    public function status(string $filter = '') : Builder
    {
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        $table = 'customers';
        $filters = explode(',', $filter);

        return $this->builder->where(function ($query) use ($filters, $table) {
            $query->whereNull($table.'.id');

            if (in_array(parent::STATUS_ACTIVE, $filters)) {
                $query->orWhereNull($table.'.deleted_at');
            }

            if (in_array(parent::STATUS_ARCHIVED, $filters)) {
                $query->orWhere(function ($query) use ($table) {
                    $query->whereNotNull($table.'.deleted_at');

                    if (! in_array($table, ['users'])) {
                        $query->where($table.'.is_deleted', '=', 0);
                    }
                });
            }

            if (in_array(parent::STATUS_DELETED, $filters)) {
                $query->orWhere($table.'.is_deleted', '=', 1);
            }
        });
    }

    /**
     * Sorts the list based on $sort.
     *
     * @param string sort formatted as column|asc
     * @return Builder
     */
    public function sort(string $sort) : Builder
    {
        $sort_col = explode('|', $sort);

        return $this->builder->orderBy($sort_col[0], $sort_col[1]);
    }

    /**
     * Returns the base query.
     *
     * @param int company_id
     * @param User $user
     * @return Builder
     * @deprecated 
     */
    public function baseQuery(int $company_id, User $user) : Builder
    {
        $query = DB::table('customers')
          
            ->select(
              
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.created_at',
                'customers.deleted_at',
                'customers.is_deleted',
                'customers.user_id',
            );

        /*
         * If the user does not have permissions to view all invoices
         * limit the user to only the invoices they have created
         */
        if (Gate::denies('view-list', Client::class)) {
            $query->where('customers.user_id', '=', $user->id);
        }

        return $query;
    }

    /**
     * Filters the query by the users company ID.
     *
     * @return Illuminate\Database\Query\Builder
     */
    public function entityFilter()
    {
        //return $this->builder->whereCompanyId(auth()->user()->company()->id);
        return $this->builder->company();
    }
}
