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

namespace App\Models;

use App\DataMapper\CompanySettings;
use App\DataMapper\FeesAndLimits;
use App\Services\Customer\CustomerService;
use App\Utils\Traits\AppSetup;
use App\Utils\Traits\GeneratesCounter;
use App\Utils\Traits\MakesDates;
use App\Utils\Traits\MakesHash;
use Exception;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laracasts\Presenter\PresentableTrait;

class Customer extends BaseModel implements HasLocalePreference
{
    use PresentableTrait;
    use MakesHash;
    use MakesDates;
    use SoftDeletes;
    use Filterable;
    use GeneratesCounter;
    use AppSetup;

    /**
     * Whitelisted fields for using from query parameters on subscriptions request.
     *
     * @var string[]
     */
    public static $subscriptions_fillable = [
        'assigned_user_id',
        'phone',
    ];
    protected $hidden = [
        'id',
        'user_id',
        'company_id',
      
    ];
    protected $fillable = [
        'assigned_user_id',
        'name',
        'phone',
        'email',
    ];
    protected $with = [
        // 'gateway_tokens',
        // 'documents',
        // 'contacts.company',
        // 'currency',
        // 'primary_contact',
        // 'country',
        // 'contacts',
        // 'shipping_country',
        // 'company',
    ];
    protected $casts = [
        'is_deleted' => 'boolean',
        //'settings' => 'object',
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];
    protected $touches = [];

    public function getEntityType()
    {
        return self::class;
    }

  
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id')->withTrashed();
    }

    public function timezone()
    {
        return Timezone::find($this->getSetting('timezone_id'));
    }

   
    public function locale()
    {
        return $this->language()->locale ?: 'en';
    }

    public function language()
    {

        $languages = Cache::get('languages');

        if (!$languages)
            $this->buildCache(true);

        return $languages->filter(function ($item) {
            return $item->id == $this->getSetting('language_id');
        })->first();

    }

    public function date_format()
    {
        $date_formats = Cache::get('date_formats');

        return $date_formats->filter(function ($item) {
            return $item->id == $this->getSetting('date_format_id');
        })->first()->format;
    }

 
    public function service(): CustomerService
    {
        return new CustomerService($this);
    }

}
