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
        try {
            $_holiday_getter = new HolidaysService($request->get('country_id'), $request->get('year'));
            $holidays = $this->holiday_instance->get_holidays();
            $current_date_status = $this->holiday_instance->get_current_date_status();
        } catch (HolidayException $ex) {
            $error_message = $ex->getMessage();
            $errors = new MessageBag();
            $errors->add('Api Error', $error_message);
            $holidays = [];
            $current_date_status = '';
        }
        dd($current_date_status);
        $params = [
            'holidays'=>$holidays,
            'countries'=>HolidaysGetter::get_countries_list(),
            'selected_country'=>$request->get('country_id'),
            'selected_year'=>$request->get('year'),
            'current_date_status'=>$current_date_status];
        return !empty($errors) ? view('search', $params)->withErrors($errors) : view('search', $params);
    }

    public function get_start_page() {
        try {
            $countries = HolidaysGetter::get_countries_list();
        } catch (HolidayException $ex) {
            $error_message = $ex->getMessage();
            $errors = new MessageBag();
            $errors->add('Api Error', $error_message);
            $countries = [];
        }
        $params = ['holidays'=>[],'countries'=>$countries,'selected_year'=>0,'selected_country'=>0];
        return !empty($errors) ? view('search',$params)->withErrors($errors) : view('search',$params);
    }
}
