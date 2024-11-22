<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'weekday_opening_hours',
                'value' => json_encode(
                    ['begin_time' => '08:00', 'end_time' => '18:00']
                )
            ],
            [
                'key' => 'weekend_opening_hours',
                'value' =>
                    json_encode(['begin_time' => '10:00', 'end_time' => '16:00'])
            ],
            ['key' => 'maximum_reservation_duration', 'value' => '120'],
            ['key' => 'student_reservation_limit', 'value' => '5'],
            ['key' => 'outsider_reservation_limit', 'value' => '2'],
            ['key' => 'points_to_ban_user', 'value' => '10'],
            ['key' => 'checkin_deadline_minutes', 'value' => '15'],
            ['key' => 'temporary_leave_deadline_minutes', 'value' => '30'],
            ['key' => 'check_in_violation_points', 'value' => '3'],
            ['key' => 'reservation_time_unit', 'value' => '15'],
        ];

        DB::table('settings')->insert($settings);
    }
}
