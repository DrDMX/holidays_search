<?php

namespace App\Http\Controllers;

use App\Services\HolidayException;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Services\HolidaysService;
use App\Services\HolidaysGetter;

class HolidayController extends Controller
{

    public function get_holidays(Request $request) {
        $request->validate([
            'country_id'=>'required|min:2|max:4',
            'year'=>'required|integer|min:2010|max:2040'
        ]);
        $holidays = [];
        $current_date_status = '';
        $holidays_count = $free_days_count = 0;
        try {
            $_holiday_service = new HolidaysService($request->get('country_id'), $request->get('year'));
            $holidays = $_holiday_service->get_holidays_by_month();
            $current_date_status = $_holiday_service->get_current_date_status();
            $holidays_count = $_holiday_service->get_holidays_count();
            $free_days_count = $_holiday_service->get_free_days_in_year();
        } catch (HolidayException) {
            $errors = new MessageBag();
            $errors->add('Api Error',  $ex->getMessage());
        } catch (\Exception $ex) { // we need this block for HolidaysService::get_free_days_in_year function
            $errors = new MessageBag();
            $errors->add('Code Error',  $ex->getMessage());
        }
        $params = [
            'holidays'=>$holidays,
            'countries'=>HolidaysGetter::get_countries_list(),
            'selected_country'=>$request->get('country_id'),
            'selected_year'=>$request->get('year'),
            'current_date_status'=>$current_date_status,
            'holidays_count'=>$holidays_count,
            'free_days_count'=>$free_days_count
        ];
        return !empty($errors) ? view('search', $params)->withErrors($errors) : view('search', $params);
    }

    public function get_start_page() {
        $countries = [];
        try {
            $countries = HolidaysGetter::get_countries_list();
        } catch (HolidayException $ex) {
            $errors = new MessageBag();
            $errors->add('Api Error', $ex->getMessage());
        }
        $params = ['holidays'=>[],'countries'=>$countries,'selected_year'=>0,'selected_country'=>0];
        return !empty($errors) ? view('search',$params)->withErrors($errors) : view('search',$params);
    }
}
