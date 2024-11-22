<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\SettingsSeeder;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetSettings()
    {
        $this->seed(SettingsSeeder::class);

        $response = $this->getJson('/api/settings');

        $response->assertStatus(200)
                 ->assertJsonStructure(['settings' => [
                     'weekdayOpeningHours',
                     'weekendOpeningHours',
                     'maximumReservationDuration',
                     'studentReservationLimit',
                     'outsiderReservationLimit',
                     'pointsToBanUser',
                     'checkinDeadlineMinutes',
                     'temporaryLeaveDeadlineMinutes',
                     'checkInViolationPoints',
                     'reservationTimeUnit',
                 ]]);
    }

    public function testUpdateSettings()
    {
        $this->seed(SettingsSeeder::class);

        $response = $this->putJson('/api/settings', [
            'settings' => [
                'weekday_opening_hours' => ['begin_time' => '09:00', 'end_time' => '17:00'],
                'maximum_reservation_duration' => 150,
            ],
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('settings', [
            'key' => 'weekday_opening_hours',
            'value' => json_encode(['begin_time' => '09:00', 'end_time' => '17:00']),
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'maximum_reservation_duration',
            'value' => '150',
        ]);
    }
}
