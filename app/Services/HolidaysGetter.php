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
    /**
     *
     */
    const API_URL = "https://kayaposoft.com/enrico/json/v2.0";
    private static $available_methods = ['getHolidaysForYear', 'getSupportedCountries', 'isPublicHoliday', 'isWorkDay'];
    private static $country_code, $year;

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
    protected static  function ProcessAndSaveHolidaysToDb(array $holidays) : array {
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
        return self::ProcessAndSaveHolidaysToDb($holidays);;
    }

    public static function get_countries_list() {
        $result = [];
        $coutries = self::send_request('getSupportedCountries');
        foreach($coutries AS $country) {
            $country_obj = new Country($country['countryCode'], $country['fullName']);
            $result[] = $country_obj;
        }
        return $result;
    }

}
