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

namespace App\Utils\Traits;

/**
 * Class UserSessionAttributes.
 */
trait UserSessionAttributes
{
    /**
     * @param $value
     */
    public function setCurrentCompanyId($value) : void
    {
        session(['current_company_id' => $value]);
    }

    /**
     * @return int
     */
    public function getCurrentCompanyId() : int
    {
        return session('current_company_id');
    }
}
