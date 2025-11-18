<?php

namespace Tests\Feature\Services;

use App\Enums\ReservationStatus;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\EventService;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        $eventService = new EventService();
        $this->reservationService = new ReservationService($eventService);
    }

    public function test_get_reservation_returns_reservation_when_exists(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $reservation = Reservation::factory()->create([
            'user_uid' => $userUuid,
        ]);

        // Act
        $result = $this->reservationService->getReservation($reservation->id, $userUuid);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($reservation->id, $result->id);
        $this->assertEquals($userUuid, $result->user_uid);
    }

    public function test_get_reservation_returns_null_when_not_exists(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();

        // Act
        $result = $this->reservationService->getReservation(999, $userUuid);

        // Assert
        $this->assertNull($result);
    }

    public function test_get_reservation_returns_null_when_user_uuid_mismatch(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $differentUserUuid = \Str::uuid()->toString();
        $reservation = Reservation::factory()->create([
            'user_uid' => $userUuid,
        ]);

        // Act
        $result = $this->reservationService->getReservation($reservation->id, $differentUserUuid);

        // Assert
        $this->assertNull($result);
    }

    public function test_get_expired_reservations_returns_only_expired_reserved_status(): void
    {
        // Arrange
        $expiredReservation1 = Reservation::factory()->create([
            'status' => ReservationStatus::RESERVED,
            'expires_at' => now()->subMinutes(10),
        ]);
        $expiredReservation2 = Reservation::factory()->create([
            'status' => ReservationStatus::RESERVED,
            'expires_at' => now()->subMinutes(5),
        ]);
        // Not expired
        Reservation::factory()->create([
            'status' => ReservationStatus::RESERVED,
            'expires_at' => now()->addMinutes(10),
        ]);
        // Expired but purchased
        Reservation::factory()->create([
            'status' => ReservationStatus::PURCHASED,
            'expires_at' => now()->subMinutes(10),
        ]);

        // Act
        $result = $this->reservationService->getExpiredReservations();

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($expiredReservation1));
        $this->assertTrue($result->contains($expiredReservation2));
    }

    public function test_reserve_creates_reservation_successfully(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $event = Event::factory()->create(['ticket_quantity' => 100]);
        $amount = 10;

        // Act
        $reservation = $this->reservationService->reserve($userUuid, $event->id, $amount);

        // Assert
        $this->assertNotNull($reservation);
        $this->assertEquals($userUuid, $reservation->user_uid);
        $this->assertEquals($event->id, $reservation->event_id);
        $this->assertEquals($amount, $reservation->amount);
        $this->assertEquals(ReservationStatus::RESERVED, $reservation->status);
        $this->assertNotNull($reservation->expires_at);

        // Check that ticket quantity was decreased
        $event->refresh();
        $this->assertEquals(90, $event->ticket_quantity);
    }

    public function test_reserve_throws_exception_when_event_not_found(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Event is not found.");

        $this->reservationService->reserve($userUuid, 999, 10);
    }

    public function test_reserve_throws_exception_when_not_enough_tickets(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $event = Event::factory()->create(['ticket_quantity' => 5]);
        $amount = 10;

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Not enough tickets available.");

        $this->reservationService->reserve($userUuid, $event->id, $amount);
    }

    public function test_purchase_updates_reservation_status_to_purchased(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $reservation = Reservation::factory()->create([
            'user_uid' => $userUuid,
            'status' => ReservationStatus::RESERVED,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act
        $result = $this->reservationService->purchase($userUuid, $reservation->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(ReservationStatus::PURCHASED, $result->status);

        $reservation->refresh();
        $this->assertEquals(ReservationStatus::PURCHASED, $reservation->status);
    }

    public function test_purchase_throws_exception_when_reservation_not_found(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Reservation is not found.");

        $this->reservationService->purchase($userUuid, 999);
    }

    public function test_purchase_throws_exception_when_reservation_expired(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $reservation = Reservation::factory()->create([
            'user_uid' => $userUuid,
            'status' => ReservationStatus::RESERVED,
            'expires_at' => now()->subMinutes(5),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Reservation has expired.");

        $this->reservationService->purchase($userUuid, $reservation->id);
    }

    public function test_purchase_throws_exception_when_already_purchased(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $reservation = Reservation::factory()->create([
            'user_uid' => $userUuid,
            'status' => ReservationStatus::PURCHASED,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("This reservation already purchased.");

        $this->reservationService->purchase($userUuid, $reservation->id);
    }

    public function test_reserve_is_atomic_transaction(): void
    {
        // Arrange
        $userUuid = \Str::uuid()->toString();
        $event = Event::factory()->create(['ticket_quantity' => 5]);

        // Act & Assert
        try {
            $this->reservationService->reserve($userUuid, $event->id, 10);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Exception should be thrown due to not enough tickets
            $this->assertEquals('Not enough tickets available.', $e->getMessage());

            // Verify that ticket quantity was not changed (transaction rolled back)
            $event->refresh();
            $this->assertEquals(5, $event->ticket_quantity);

            // Verify that no reservation was created
            $this->assertDatabaseCount('reservations', 0);
        }
    }
}
