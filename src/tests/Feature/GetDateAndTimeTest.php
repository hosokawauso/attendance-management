<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Staff;

class GetDateAndTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_date_and_time_shown_in_ui_format()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 11, 15, 46, 0, 'Asia/Tokyo'));

        $staff = Staff::factory()->create();
        $this->actingAs($staff);

        $response = $this->get(route('attendance'));

        $expectedDate = now('Asia/Tokyo')->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)');
        $expectedTime = now('Asia/Tokyo')->format('H:i');

        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        Carbon::setTestNow();
    }

}
