<?php
namespace App\Services;

use App\Services\HolidayException;
use App\Services\DayStatuses;
use App\Services\HolidaysGetter;
use App\Models\Holiday;

class HolidaysService {
    protected $country_code, $year;

    public function __construct(string $country_code, integer $year) {
        $this->country_code;
        $this->year;
        $this->verify_country_code($this->country_code);
    }

    /**
     * @return array|null
     */
    public function get_holidays_by_month() : ?array {
        return $this->group_by_month($this->get_holidays_from_db() ?? $this->get_holidays_from_api());
    }

    /**
     * @return array
     * @throws \App\Services\HolidayException
     */
    public function get_holidays() : array {
        return  $this->get_holidays_from_db() ?? HolidaysGetter::get_holidays_list($this->country_code, $this->year);
    }

    /**
     * @return string
     * @throws \App\Services\HolidayException
     */
    public function get_current_date_status() : string {
        $date = new \DateTime();
        if (HolidaysGetter::is_work_day($date, $this->country_code)) {
            $result = DayStatuses::$work;
        } elseif (HolidaysGetter::is_holiday($date, $this->country_code)) {
            $result = DayStatuses::$holiday;
        } else {
            $result = DayStatuses::$free;
        }
        return $result;
    }

    /**
     * @param array $holidays
     * @return array|null
     * @throws \Exception
     */
    protected function group_by_month(array $holidays) : ?array {
        $result = [];
        foreach ($holidays AS $holiday) {
            $date = new \DateTime($holiday->date);
            $current_month = $date->format('F');
            $result[$current_month][] = $holiday;
        }
        return $result;
    }

    /**
     * @param string $country_code
     * @throws \App\Services\HolidayException
     */
    protected function verify_country_code(string $country_code) : void {
        $country_codes = HolidaysGetter::get_countries_list(true);
        if (!in_array($country_code, $country_codes)) {
            throw new HolidayException("Wrong Country Code for API request.");
        }
    }

    /**
     * @return array|null
     */
    protected function get_holidays_from_db() : ?array {
        $holidays = Holiday::where('country_code', $this->country_code)->where('year', $this->year)->get()->all();
        return !empty($holidays) ? $holidays : null;
    }

}
