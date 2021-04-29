<?php

namespace Tests\Feature;

use App\Services\Country;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_root() {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function search_test_get() {
        $response = $this->get('/search');
        $response->assertStatus(200);
    }

    public function search_test_post() {
        $country = New Country('ru', 'Russian Federation');
        $holiday = New \stdClass();
        $holiday->date = '2020-01-01';
        $holiday->en_name = 'New Year';
        $holiday->original_name = 'Новый Год';

        $mock_data = [
            'holidays'=>['December'=>[$holiday]],
            'countries'=>[$country],
            'selected_country'=>'ru',
            'selected_year'=>2020,
            'current_date_status'=>'Holliday',
            'holidays_count'=>1,
            'free_days_count'=>111
        ];
        $response = $this->post('/search',$mock_data);
        $response->assertStatus(200);
    }
}
