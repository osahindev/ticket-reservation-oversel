<?php

namespace Tests\Feature\Services;

use App\Models\Event;
use App\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventService $eventService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventService = new EventService();
    }

    public function test_get_all_events_returns_events_ordered_by_created_at_desc(): void
    {
        // Arrange
        $event1 = Event::factory()->create(['created_at' => now()->subDays(2)]);
        $event2 = Event::factory()->create(['created_at' => now()->subDays(1)]);
        $event3 = Event::factory()->create(['created_at' => now()]);

        // Act
        $events = $this->eventService->getAllEvents();

        // Assert
        $this->assertCount(3, $events);
        $this->assertEquals($event3->id, $events->first()->id);
        $this->assertEquals($event1->id, $events->last()->id);
    }

    public function test_get_event_for_update_returns_event_when_exists(): void
    {
        // Arrange
        $event = Event::factory()->create();

        // Act
        $result = $this->eventService->getEventForUpdate($event->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($event->id, $result->id);
    }

    public function test_get_event_for_update_returns_null_when_not_exists(): void
    {
        // Act
        $result = $this->eventService->getEventForUpdate(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_increment_ticket_quantity_increases_quantity(): void
    {
        // Arrange
        $event = Event::factory()->create(['ticket_quantity' => 100]);
        $amountToIncrement = 50;

        // Act
        $this->eventService->incrementTicketQuantity($event, $amountToIncrement);

        // Assert
        $event->refresh();
        $this->assertEquals(150, $event->ticket_quantity);
    }

    public function test_decrease_ticket_quantity_decreases_quantity(): void
    {
        // Arrange
        $event = Event::factory()->create(['ticket_quantity' => 100]);
        $amountToDecrease = 30;

        // Act
        $this->eventService->decreaseTicketQuantity($event, $amountToDecrease);

        // Assert
        $event->refresh();
        $this->assertEquals(70, $event->ticket_quantity);
    }

    public function test_get_all_events_returns_empty_collection_when_no_events(): void
    {
        // Act
        $events = $this->eventService->getAllEvents();

        // Assert
        $this->assertCount(0, $events);
    }
}
