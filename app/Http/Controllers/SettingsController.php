<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function getSettings()
    {
        $settings = DB::table('settings')->pluck('value', 'key');

        // 解析特定 JSON 字段
        $parsedSettings = [
            'weekdayOpeningHours' => json_decode($settings['weekday_opening_hours'], true),
            'weekendOpeningHours' => json_decode($settings['weekend_opening_hours'], true),
            'maximumReservationDuration' => (int) $settings['maximum_reservation_duration'],
            'studentReservationLimit' => (int) $settings['student_reservation_limit'],
            'outsiderReservationLimit' => (int) $settings['outsider_reservation_limit'],
            'pointsToBanUser' => (int) $settings['points_to_ban_user'],
            'checkinDeadlineMinutes' => (int) $settings['checkin_deadline_minutes'],
            'temporaryLeaveDeadlineMinutes' => (int) $settings['temporary_leave_deadline_minutes'],
            'checkInViolationPoints' => (int) $settings['check_in_violation_points'],
            'reservationTimeUnit' => (int) $settings['reservation_time_unit'],
        ];

        return response()->json(['settings' => $parsedSettings], 200);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($data['settings'] as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return response()->noContent();
    }
}
