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

namespace App\Http\Requests\Customer;

use App\Http\Requests\Request;
use App\Http\ValidationRules\Ninja\CanStoreClientsRule;
use App\Http\ValidationRules\ValidClientGroupSettingsRule;
use App\Models\Customer;
use App\Models\GroupSetting;
use App\Utils\Traits\MakesHash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return auth()->user()->can('create', Customer::class);
    }

    public function rules()
    {
        $rules = [];

        $rules['name'] = 'required';
        if ($this->number)
            $rules['number'] = Rule::unique('customers')->where('company_id', auth()->user()->company()->id);

        return $this->globalRules($rules);
    }


    public function messages()
    {
        return [
            'name.required' => ctrans('validation.required', ['attribute' => 'name']),
        ];
    }
}
