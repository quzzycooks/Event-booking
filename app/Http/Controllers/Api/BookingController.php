<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = $this->getUser()
            ->bookings()
            ->with(['event.organizer'])
            ->latest()
            ->paginate();

        return $this->response(
            message: 'Bookings retrieved successfully',
            data: BookingResource::collection($bookings)->response()->getData(true)
        );
    }

    public function create(StoreBookingRequest $request): JsonResponse
    {
        $user = $this->getUser();
        $validated = $request->validated();

        try {
            // Use database transaction with row locking to prevent race conditions
            $booking = DB::transaction(function () use ($user, $validated) {
                $event = Event::whereId($validated['event_id'])
                    ->published()
                    ->lockForUpdate()
                    ->first();

                if (! $event) throw new \Exception('Event not found');

                if ($event->isCancelled()) {
                    throw new \Exception('Cannot book a cancelled event');
                }

                if ($event->isPast()) {
                    throw new \Exception('Cannot book a past event');
                }

                // block duplicate
                $existingBooking = Booking::whereEventId($event->id)
                    ->whereUserId($user->id)
                    ->exists();

                if ($existingBooking) {
                    throw new \Exception('You have already booked this event');
                }

                // Check seat availability
                if ($event->remaining_seats < $validated['quantity']) {
                    throw new \Exception("Not enough seats available. {$event->remaining_seats} seat(s) remaining");
                }

                $event->decrement('remaining_seats', $validated['quantity']);

                // Create booking
                return Booking::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'quantity' => $validated['quantity'],
                    'status' => 'confirmed'
                ]);
            });

            $booking->load(['event.organizer']);

            return $this->response(
                message: 'Booking created successfully',
                data: new BookingResource($booking),
                status: 201
            );
        } catch (\Exception $e) {
            return $this->response(
                message: $e->getMessage(),
                status: 400
            );
        }
    }

    public function get(int $id): JsonResponse
    {
        $booking = $this->getUser()
            ->bookings()
            ->with(['event.organizer'])
            ->find($id);
        if (! $booking) {
            return $this->response(
                message: 'Booking not found',
                status: 404
            );
        }

        return $this->response(
            message: 'Booking details retrieved successfully',
            data: new BookingResource($booking)
        );
    }

    public function delete(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $booking = $this->getUser()
                    ->bookings()
                    ->lockForUpdate()
                    ->find($id);

                if (! $booking) {
                    throw new \Exception('Booking not found');
                }

                if ($booking->isCancelled()) {
                    throw new \Exception('Booking is already cancelled');
                }

                $event = Event::lockForUpdate()->find($booking->event_id);
                $event->increment('remaining_seats', $booking->quantity);

                $booking->update(['status' => 'cancelled']);
            });

            return $this->response(
                message: 'Booking cancelled successfully'
            );

        } catch (\Exception $e) {
            return $this->response(
                message: $e->getMessage(),
                status: 400
            );
        }
    }
}
