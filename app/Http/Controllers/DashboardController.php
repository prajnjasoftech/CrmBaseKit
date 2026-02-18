<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $upcomingFollowUps = FollowUp::query()
            ->with(['followable', 'creator:id,name'])
            ->where('status', FollowUp::STATUS_PENDING)
            ->where('follow_up_date', '>=', Carbon::today())
            ->where('follow_up_date', '<=', Carbon::today()->addDays(7))
            ->orderBy('follow_up_date')
            ->limit(10)
            ->get()
            ->map(function (FollowUp $followUp): array {
                $followable = $followUp->followable;
                $parentType = $followable instanceof Lead ? 'lead' : 'customer';

                /** @var Lead|Customer|null $followable */
                return [
                    'id' => $followUp->id,
                    'follow_up_date' => $followUp->follow_up_date->toDateString(),
                    'notes' => $followUp->notes,
                    'status' => $followUp->status,
                    'parent_type' => $parentType,
                    'parent_id' => $followable->id ?? null,
                    'parent_name' => $followable->name ?? 'Unknown',
                    'creator' => $followUp->creator,
                    'is_today' => $followUp->follow_up_date->isToday(),
                ];
            });

        $overdueFollowUps = FollowUp::query()
            ->with(['followable', 'creator:id,name'])
            ->where('status', FollowUp::STATUS_PENDING)
            ->where('follow_up_date', '<', Carbon::today())
            ->orderBy('follow_up_date')
            ->limit(10)
            ->get()
            ->map(function (FollowUp $followUp): array {
                $followable = $followUp->followable;
                $parentType = $followable instanceof Lead ? 'lead' : 'customer';

                /** @var Lead|Customer|null $followable */
                return [
                    'id' => $followUp->id,
                    'follow_up_date' => $followUp->follow_up_date->toDateString(),
                    'notes' => $followUp->notes,
                    'status' => $followUp->status,
                    'parent_type' => $parentType,
                    'parent_id' => $followable->id ?? null,
                    'parent_name' => $followable->name ?? 'Unknown',
                    'creator' => $followUp->creator,
                ];
            });

        $stats = [
            'total_leads' => Lead::count(),
            'total_customers' => Customer::count(),
            'pending_follow_ups' => FollowUp::where('status', FollowUp::STATUS_PENDING)->count(),
            'overdue_follow_ups' => FollowUp::where('status', FollowUp::STATUS_PENDING)
                ->where('follow_up_date', '<', Carbon::today())
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'upcomingFollowUps' => $upcomingFollowUps,
            'overdueFollowUps' => $overdueFollowUps,
            'stats' => $stats,
        ]);
    }
}
