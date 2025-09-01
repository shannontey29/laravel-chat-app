<?php

namespace Tests\Unit;

use App\Helpers\Helper;
use Carbon\Carbon;
use Tests\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function user_last_activity_status_returns_online_for_recent_activity()
    {
        $timestamp = Carbon::now()->subSeconds(3);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $this->assertEquals('Online', $status);
    }

    /** @test */
    public function user_last_activity_status_returns_online_for_current_time()
    {
        $timestamp = Carbon::now();
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $this->assertEquals('Online', $status);
    }

    /** @test */
    public function user_last_activity_status_returns_today_format_for_today()
    {
        $timestamp = Carbon::now()->subHours(2);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $expected = "Last seen today at {$timestamp->format('H:i')}";
        $this->assertEquals($expected, $status);
    }

    /** @test */
    public function user_last_activity_status_returns_yesterday_format_for_yesterday()
    {
        $timestamp = Carbon::yesterday()->setHour(14)->setMinute(30);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $expected = "Last seen yesterday at {$timestamp->format('H:i')}";
        $this->assertEquals($expected, $status);
    }

    /** @test */
    public function user_last_activity_status_returns_full_date_format_for_older_dates()
    {
        $timestamp = Carbon::now()->subDays(5)->setHour(10)->setMinute(15);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $expected = "Last seen at {$timestamp->format('d/m/Y H:i')}";
        $this->assertEquals($expected, $status);
    }

    /** @test */
    public function user_last_activity_status_returns_null_for_null_timestamp()
    {
        $status = Helper::userLastActivityStatus(null);
        
        // When timestamp is null, the function returns the lastSeenFormat which evaluates to "Last seen at "
        $this->assertEquals('Last seen at ', $status);
    }

    /** @test */
    public function user_last_activity_status_boundary_test_at_five_seconds()
    {
        $timestamp = Carbon::now()->subSeconds(5);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        // At exactly 5 seconds, should be "Last seen today at..."
        $expected = "Last seen today at {$timestamp->format('H:i')}";
        $this->assertEquals($expected, $status);
    }

    /** @test */
    public function user_last_activity_status_boundary_test_at_six_seconds()
    {
        $timestamp = Carbon::now()->subSeconds(6);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        // At 6 seconds, should be "Last seen today at..."
        $expected = "Last seen today at {$timestamp->format('H:i')}";
        $this->assertEquals($expected, $status);
    }

    /** @test */
    public function user_last_activity_status_boundary_test_at_four_seconds()
    {
        $timestamp = Carbon::now()->subSeconds(4);
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        // At 4 seconds, should be "Online"
        $this->assertEquals('Online', $status);
    }

    /** @test */
    public function user_last_activity_status_handles_specific_date_formats()
    {
        // Test specific date format
        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', '2023-01-15 14:30:45');
        
        $status = Helper::userLastActivityStatus($timestamp);
        
        $expected = "Last seen at 15/01/2023 14:30";
        $this->assertEquals($expected, $status);
    }
}
