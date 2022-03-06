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

use App\Jobs\Util\UnlinkFile;
use App\Jobs\Util\UploadAvatar;
use Illuminate\Support\Facades\Storage;

/**
 * Class Uploadable.
 */
trait Uploadable
{
    public function removeLogo($company)
    {

        if (Storage::exists($company->settings->company_logo)) {
            UnlinkFile::dispatchNow(config('filesystems.default'), $company->settings->company_logo);
        }
    }

    public function uploadLogo($file, $company, $entity)
    {
        if ($file) {
            $path = UploadAvatar::dispatchNow($file, $company->company_key);

            if ($path) {
                $settings = $entity->settings;
                $settings->company_logo = $path;
                $entity->settings = $settings;
                $entity->save();
            }
        }

    }
}
