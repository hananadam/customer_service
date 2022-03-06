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

namespace App\Utils\Traits\Reservation;

use App\Models\Reservation;

trait ActionsReservation
{
    public function reservationDeletable($reservation): bool
    {
        if ($reservation->status_id <= Reservation::STATUS_CANCELLED &&
            $reservation->is_deleted == false &&
            $reservation->deleted_at == null &&
            $reservation->balance == 0) {
            return true;
        }

        return false;
    }

    public function reservationCancellable($reservation): bool
    {
        if (($reservation->status_id == Reservation::STATUS_CONFIRMED ||
                $reservation->status_id == Reservation::STATUS_PARTIAL) &&
            $reservation->is_deleted == false &&
            $reservation->deleted_at == null) {
            return true;
        }

        return false;
    }

    public function reservationConfirmed($reservation): bool
    {
        if (($reservation->status_id == Reservation::STATUS_DRAFT) &&
            $reservation->is_deleted == false &&
            $reservation->deleted_at == null) {
            return true;
        }

        return false;
    }

    public function reservationCheckedIn($reservation): bool
    {
        if (($reservation->status_id == Reservation::STATUS_CONFIRMED) &&
            $reservation->is_deleted == false &&
            $reservation->deleted_at == null) {
            return true;
        }

        return false;
    }

    public function reservationCheckedOut($reservation): bool
    {
        if (($reservation->status_id == Reservation::STATUS_CHECKED_IN) &&
            $reservation->is_deleted == false &&
            $reservation->deleted_at == null) {
            return true;
        }

        return false;
    }
}
