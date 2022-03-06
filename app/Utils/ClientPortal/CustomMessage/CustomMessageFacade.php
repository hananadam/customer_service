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

namespace App\Utils\ClientPortal\CustomMessage;

use Illuminate\Support\Facades\Facade;

class CustomMessageFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'customMessage';
    }
}
