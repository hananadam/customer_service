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

namespace App\Utils\Traits\Room;

use App\Models\Room;

trait ActionsRoom
{
    public function roomDeletable($room): bool
    {
        if ($room->status_id <= Room::STATUS_OUT_OF_SERVICE &&
            $room->is_deleted == false &&
            $room->deleted_at == null ) {
            return true;
        }

        return false;
    }

    public function roomReservable($room): bool
    {
        if ($room->status_id == Reservation::STATUS_IN_CLEAN &&
            $room->is_deleted == false &&
            $room->deleted_at == null) {
            return true;
        }

        return false;
    }

}
