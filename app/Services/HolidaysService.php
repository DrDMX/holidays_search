<?php
namespace App\Services;

use App\Services\HolidayException;
use App\Services\DayStatuses;
use App\Services\HolidaysGetter;
use App\Models\Holiday;


class HolidaysService {
    protected $country_code, $year;

    public function get_holidays_by_month() {
        return  $this->group_by_month($this->get_holidays_from_db() ?? $this->get_holidays_from_api());
    }

    public function get_holidays() {
        return  $this->get_holidays_from_db() ?? HolidaysGetter::get_holidays_list();
    }
}
