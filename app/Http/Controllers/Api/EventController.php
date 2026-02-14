<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $query = Event::with('organizer');

        if ($user && $user->isOrganizer()) {
            $query->where('organizer_id', $user->id);
        } else {
            $query->where('status', 'published');

            if ($request->date) {
                $query->whereDate('event_date', $request->get('date'));
            }

            if ($request->location) {
                $query->whereLike('location', "%{$request->location}%");
            }
        }

        $events = $query->latest()->paginate(15);

        return $this->response(
            message: 'Events retrieved successfully',
            data: EventResource::collection($events)->response()->getData(true),
        );
    }

    public function create(StoreEventRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $event = $this->getUser()->events()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $validated['event_date'],
            'price' => $validated['price'],
            'total_seats' => $validated['total_seats'],
            'remaining_seats' => $validated['total_seats'],
            'status' => 'draft'
        ]);

        return $this->response(
            message: 'Event created successfully',
            data: new EventResource($event->load('organizer')),
            status: 201
        );
    }

    public function get(int $id): JsonResponse
    {
        $user = $this->getUser();

        if ($user && $user->isOrganizer()) {
            $event = Event::with(['organizer'])->withCount([
                'bookings' => function ($query) {
                    $query->where('status', 'confirmed');
                }
            ])->whereOrganizerId($user->id)->find($id);
        } else {
            $event = Event::with(['organizer'])->published()->find($id);
        }

        if (! $event) {
            return $this->response(
                message: 'Event not found',
                status: 404
            );
        }

        return $this->response(
            message: 'Event details',
            data: new EventResource($event)
        );
    }

    public function bookings(int $id): JsonResponse
    {
        $user = $this->getUser();

        $event = $user->events()->find($id);
        if (! $event) {
            return $this->response(
                message: 'Event not found',
                status: 404
            );
        }

        $bookings = $event->bookings()->with('user')->latest()->paginate();

        return $this->response(
            message: 'event bookings',
            data: $bookings
        );
    }

    public function update(UpdateEventRequest $request, int $id): JsonResponse
    {
        $event = $this->getUser()->events()->find($id);
        if (! $event) {
            return $this->response(
                message: 'Event not found or unauthorized',
                status: 404
            );
        }

        $validated = $request->validated();

        if (isset($validated['total_seats'])) {
            $bookedSeats = $event->total_seats - $event->remaining_seats;
            $newTotalSeats = $validated['total_seats'];

            if ($newTotalSeats < $bookedSeats) {
                return $this->response(
                    message: 'Cannot reduce total seats below already booked seats (' . $bookedSeats . ')',
                    status: 422
                );
            }

            $validated['remaining_seats'] = $newTotalSeats - $bookedSeats;
        }

        $event->update($validated);

        return $this->response(
            message: 'Event updated successfully',
            data: new EventResource($event->fresh())
        );
    }

    public function delete(int $id): JsonResponse
    {
        $event = $this->getUser()->events()->find($id);
        if (! $event) {
            return $this->response(
                message: 'Event not found or unauthorized',
                status: 404
            );
        }

        // Check if event has bookings
        if ($event->bookings()->where('status', 'confirmed')->exists()) {
            return $this->response(
                message: 'Cannot delete event with confirmed bookings. Please cancel the event instead.',
                status: 422
            );
        }

        $event->delete();

        return $this->response(
            message: 'Event deleted successfully'
        );
    }
}
