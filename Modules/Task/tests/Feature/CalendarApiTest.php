<?php

namespace Modules\Task\Tests\Feature;

use Tests\TestCase;
use Modules\Task\app\Models\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;

class CalendarApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable the Task module
        $this->artisan('module:enable', ['module' => 'Task']);
    }

    #[Test]
    public function it_can_list_calendar_events()
    {
        $response = $this->getJson('/api/calendar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_create_calendar_event()
    {
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'event_type' => 'event',
            'participant_id' => 1,
            'participant_type' => 'lecturer',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ];

        $response = $this->postJson('/api/calendar', $eventData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'start_time',
                        'end_time',
                        'event_type',
                        'participant_id',
                        'participant_type',
                        'creator_id',
                        'creator_type',
                        'created_at'
                    ],
                    'message'
                ]);
    }

    #[Test]
    public function it_can_show_calendar_event()
    {
        $event = Calendar::factory()->create();

        $response = $this->getJson("/api/calendar/{$event->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_update_calendar_event()
    {
        $event = Calendar::factory()->create();
        $updateData = [
            'title' => 'Updated Event',
            'description' => 'Updated Description',
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'event_type' => 'event',
            'participant_id' => 1,
            'participant_type' => 'lecturer',
            'creator_id' => 1,
            'creator_type' => 'lecturer'
        ];

        $response = $this->putJson("/api/calendar/{$event->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_delete_calendar_event()
    {
        $event = Calendar::factory()->create();

        $response = $this->deleteJson("/api/calendar/{$event->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_calendar_view()
    {
        $response = $this->getJson('/api/calendar/view?view_type=monthly&date=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_calendar_conflicts()
    {
        $response = $this->getJson('/api/calendar/conflicts?' . http_build_query([
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'participant_id' => 1,
            'participant_type' => 'lecturer'
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_calendar_reminders()
    {
        $response = $this->getJson('/api/calendar/reminders?' . http_build_query([
            'participant_id' => 1,
            'participant_type' => 'lecturer'
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_set_calendar_reminder()
    {
        $event = Calendar::factory()->create();
        
        $reminderData = [
            'event_id' => $event->id,
            'reminder_time' => '15_minutes_before',
            'reminder_type' => 'email'
        ];

        $response = $this->postJson('/api/calendar/reminders', $reminderData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_events_by_date()
    {
        $response = $this->getJson('/api/calendar/events/by-date?date=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_events_by_range()
    {
        $response = $this->getJson('/api/calendar/events/by-range?' . http_build_query([
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addWeek()->format('Y-m-d')
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_recurring_events()
    {
        $response = $this->getJson('/api/calendar/events/recurring');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_upcoming_events()
    {
        $response = $this->getJson('/api/calendar/events/upcoming?' . http_build_query([
            'participant_id' => 1,
            'participant_type' => 'lecturer',
            'limit' => 10
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_overdue_events()
    {
        $response = $this->getJson('/api/calendar/events/overdue?' . http_build_query([
            'participant_id' => 1,
            'participant_type' => 'lecturer'
        ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_events_by_type()
    {
        $response = $this->getJson('/api/calendar/events/by-type?type=event');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_events_count_by_status()
    {
        $response = $this->getJson('/api/calendar/events/count-by-status');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }

    #[Test]
    public function it_can_get_calendar_statistics()
    {
        $response = $this->getJson('/api/calendar/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);
    }
}
