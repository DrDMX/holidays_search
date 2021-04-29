<?php

namespace App\Services;
use App\Services\HolidayException;
use App\Services\Country;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Holiday;

/**
 * Class HolidaysGetter get holidays through API and save them to db
 * @package App\Services
 */
class HolidaysGetter {

    const API_URL = "https://kayaposoft.com/enrico/json/v2.0";
    private static $available_methods = ['getHolidaysForYear', 'getSupportedCountries', 'isPublicHoliday', 'isWorkDay'];
    private static $country_code, $year;
    private static $countries = [];

    /**
     * @param string $action
     * @param array $params
     * @return array
     * @throws \App\Services\HolidayException
     */
    protected static  function send_request(string $action, array $params = []) : array {
        if (!in_array($action, self::$available_methods)) {
            throw new HolidayException("Wrong method for API call");
        }
        $url = self::API_URL."?action=".$action;
        foreach($params AS $name=>$value) {
            $url .= "&{$name}={$value}";
        }
        $response = Http::get($url);
        $response_array = json_decode($response->body(), true);
        if ($response->status() !== 200) {
            $error = $response_array['error'] ?? 'Sending request error.';
            throw new HolidayException($error);
        }
        return $response_array;
    }

    /**
     * @param array $holidays
     * @return array
     */
    protected static function process_and_save_holidays_to_db(array $holidays) : array {
        $result = [];

        foreach($holidays AS $holiday) {
            $original_name = $en_name = "";
            $date = "{$holiday['date']['year']}-{$holiday['date']['month']}-{$holiday['date']['day']}";
            foreach ($holiday['name'] AS $name) {
                if ($name['lang']==='en') {
                    $en_name = $name['text'];
                } else {
                    $original_name = $name['text'];
                }
                if (empty($original_name)) {
                    $original_name = $en_name;
                }
            }
            $holiday = new Holiday();
            $holiday->date = $date;
            $holiday->original_name = $original_name;
            $holiday->en_name = $en_name;
            $holiday->country_code = self::$country_code;
            $holiday->year = self::$year;
            $holiday->save();
            $result[] = $holiday;
        }
        return $result;
    }

    /**
     * @param string $country_code
     * @param integer $year
     * @return array
     * @throws \App\Services\HolidayException
     */
    public static function get_holidays_list(string $country_code, integer $year) : array {
        self::$country_code = $country_code;
        self::$year = $year;
        $holidays = self::send_request('getHolidaysForYear', ['year'=>self::$year, 'country'=>self::$country_code]);
        return self::process_and_save_holidays_to_db($holidays);;
    }

    /**
     * @return array
     * @throws \App\Services\HolidayException
     */
    public static function get_countries_list(bool $get_codes = false) : array {
        if (!empty(self::$countries)) {
            return self::$countries;
        } else {
            $result = [];
            $coutries = self::send_request('getSupportedCountries');
            foreach ($coutries as $country) {
                if ($get_codes) {
                    $result[] = $country['countryCode'];
                } else {
                    $country_obj = new Country($country['countryCode'], $country['fullName']);
                    $result[] = $country_obj;
                }
            }
            return $result;
        }
    }

    /**
     * @param \DateTime $date
     * @param string $country_code
     * @return bool
     * @throws \App\Services\HolidayException
     */
    public static function is_work_day(\DateTime $date, string $country_code) : bool {
        $date_str = $date->format('d-m-Y');
        $response = self::send_request('isWorkDay', ['date'=>$date_str, 'coutry'=>$country_code]);
        return $response['isWorkDay'];
    }

    /**
     * @param \DateTime $date
     * @param string $country_code
     * @return bool
     * @throws \App\Services\HolidayException
     */
    public static function is_holiday(\DateTime $date, string $country_code) : bool {
        $date_str = $date->format('d-m-Y');
        $response = self::send_request('isPublicHoliday', ['date'=>$date_str, 'coutry'=>$country_code]);
        return $response['isPublicHoliday'];
    }

}
