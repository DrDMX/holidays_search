<?php

namespace Tests\Unit;

use App\Services\HolidaysGetter;
use App\Services\HolidaysService;
use Tests\TestCase;


class UnitTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    protected function get_random_date_and_code_for_holiday() {
        $countries_codes = HolidaysGetter::get_countries_list(true);
        $code = $countries_codes[rand(0, count($countries_codes))];
        $holidays = HolidaysGetter::get_holidays_list($code, rand(2015, 2030));
        $holiday = $holidays[rand(0, count($holidays))];
        $date_str = $holiday->date;
        $date = new \DateTime($date_str);
        return ['date'=>$date, 'code'=>$code];
    }

    public function test_example() {
        $this->assertTrue(true);
    }

    public function test_is_holiday() {
        $data = $this->get_random_date_and_code_for_holiday();
        $this->assertTrue(HolidaysGetter::is_holiday($data['date'], $data['code']));
    }

    public function test_is_work_day() {
        $data = $this->get_random_date_and_code_for_holiday();
        $this->assertFalse(HolidaysGetter::is_work_day($data['date'], $data['code']));
    }

    public function test_check_free_days_in_year() {
        $holidays_service = New HolidaysService('rus', 2020);
        //We know that in 2020 for russia we have 118 free days, we can use this information in test
        $free_days = $holidays_service->get_free_days_in_year();
        $this->assertTrue($free_days === 118);
    }

    public function test_check_countries_list() {
        $countries_codes = HolidaysGetter::get_countries_list();
        $this->assertIsArray($countries_codes);
        $country_obj = $countries_codes[0];
        $this->assertIsObject($country_obj);
        $this->assertObjectHasAttribute('code', $country_obj);
        $this->assertObjectHasAttribute('name', $country_obj);

    }
}
