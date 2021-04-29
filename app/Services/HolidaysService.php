<?php
namespace App\Services;

use App\Services\HolidayException;
use App\Services\DayStatuses;
use App\Services\HolidaysGetter;
use App\Models\Holiday;

class HolidaysService {
    protected $country_code, $year;
    protected $holidays = [];
    public function __construct(string $country_code, int $year) {
        $this->country_code = $country_code;
        $this->year = $year;
        $this->verify_country_code($this->country_code);
        $this->holidays = $this->load_holidays();
    }

    /**
     * @return array|null
     */
    public function get_holidays_by_month() : ?array {
        return $this->group_by_month($this->holidays);
    }

    /**
     * @return array
     * @throws \App\Services\HolidayException
     */
    public function get_holidays() : array {
        return  $this->holidays;
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
     * @return int
     */
    public function get_holidays_count() {
        return count($this->holidays);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function get_free_days_in_year() {
        $startdate = new \DateTime('01/01/'.$this->year);
        $enddate = new \DateTime('12/31/'.$this->year);
        $interval = new \DateInterval('P1D');
        $free_days = 0;
        $holidays_dates_array = array_map(function($el) {
           return $el->date;
        }, $this->holidays);
        do {
            $dow = $startdate->format('w');
            $date_str = $startdate->format('Y-m-d');
            if (in_array($dow, [0,6]) || in_array($date_str, $holidays_dates_array)) {
                $free_days++;
            }
            $startdate->add($interval);
        } while ($startdate <> $enddate);

        return $free_days;
    }

    /**
     * @return array
     * @throws \App\Services\HolidayException
     */
    protected function load_holidays() {
        return $this->get_holidays_from_db() ?? HolidaysGetter::get_holidays_list($this->country_code, $this->year);
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
