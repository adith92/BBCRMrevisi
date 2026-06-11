<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function clients(Request $request)
    {
        $user = auth()->user();
        $query = Client::query();

        if ($user->isSales()) {
            $query->where('assigned_sales_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
            $query->whereIn('assigned_sales_id', $teamIds);
        }

        $clients = $query->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->company_name,
                    'status' => $c->status,
                    'contact' => $c->primary_contact_name,
                    'phone' => $c->primary_contact_phone,
                    'email' => $c->primary_contact_email,
                ];
            });

        return response()->json($clients);
    }

    public function bookings(Request $request)
    {
        $user = auth()->user();
        $query = Booking::with(['client', 'vehicle']);

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
            $query->whereIn('sales_id', $teamIds);
        }

        $bookings = $query->whereIn('status', ['confirmed', 'on_trip'])
            ->latest()
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_number' => $b->booking_number,
                    'client_name' => $b->client->company_name ?? 'N/A',
                    'vehicle' => $b->vehicle->plate_number ?? 'N/A',
                    'status' => $b->status,
                    'start_date' => $b->start_date ? $b->start_date->format('Y-m-d') : null,
                    'price' => (float)$b->price,
                ];
            });

        return response()->json($bookings);
    }

    public function fleet(Request $request)
    {
        // Fetch all fleet but with their status for the modal
        $vehicles = Vehicle::with('pool')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plate_number' => $v->plate_number,
                    'type' => $v->type,
                    'pool' => $v->pool->name ?? 'N/A',
                    'status' => $v->status,
                ];
            });

        return response()->json($vehicles);
    }

    public function drivers(Request $request)
    {
        // Fetch all drivers with their status for the modal
        $drivers = Driver::with('pool')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'phone' => $d->phone,
                    'pool' => $d->pool->name ?? 'N/A',
                    'status' => $d->status,
                ];
            });

        return response()->json($drivers);
    }

    public function opportunities(Request $request)
    {
        $user = auth()->user();
        $stage = $request->get('stage', 'won');
        
        $query = Opportunity::with(['client', 'sales'])->where('stage', $stage);

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
            $query->whereIn('sales_id', $teamIds);
        }

        $opportunities = $query->latest()
            ->get()
            ->map(function ($opp) {
                return [
                    'id' => $opp->id,
                    'opp_number' => $opp->opp_number,
                    'title' => $opp->title,
                    'client_name' => $opp->client->company_name ?? 'N/A',
                    'sales_name' => $opp->sales->name ?? 'Unknown',
                    'value' => (float)($opp->final_value ?? $opp->estimated_value ?? 0),
                    'stage' => $opp->stage,
                ];
            });

        return response()->json($opportunities);
    }
}
