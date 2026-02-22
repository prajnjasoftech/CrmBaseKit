<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['super-admin', 'admin', 'manager']);

        $upcomingFollowUps = FollowUp::query()
            ->with(['followable', 'creator:id,name'])
            ->where('status', FollowUp::STATUS_PENDING)
            ->where('follow_up_date', '>=', Carbon::today())
            ->where('follow_up_date', '<=', Carbon::today()->addDays(7))
            ->when(! $isManager, function (Builder $query) use ($user): void {
                $query->where(function (Builder $q) use ($user): void {
                    $q->whereHasMorph('followable', [Lead::class], function (Builder $leadQuery) use ($user): void {
                        $leadQuery->where('assigned_to', $user->id);
                    })->orWhereHasMorph('followable', [Customer::class], function (Builder $customerQuery) use ($user): void {
                        $customerQuery->where('assigned_to', $user->id);
                    });
                });
            })
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
            ->when(! $isManager, function (Builder $query) use ($user): void {
                $query->where(function (Builder $q) use ($user): void {
                    $q->whereHasMorph('followable', [Lead::class], function (Builder $leadQuery) use ($user): void {
                        $leadQuery->where('assigned_to', $user->id);
                    })->orWhereHasMorph('followable', [Customer::class], function (Builder $customerQuery) use ($user): void {
                        $customerQuery->where('assigned_to', $user->id);
                    });
                });
            })
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

        // Stats also filtered by role
        $pendingQuery = FollowUp::where('status', FollowUp::STATUS_PENDING);
        $overdueQuery = FollowUp::where('status', FollowUp::STATUS_PENDING)
            ->where('follow_up_date', '<', Carbon::today());

        if (! $isManager) {
            $pendingQuery->where(function (Builder $q) use ($user): void {
                $q->whereHasMorph('followable', [Lead::class], function (Builder $leadQuery) use ($user): void {
                    $leadQuery->where('assigned_to', $user->id);
                })->orWhereHasMorph('followable', [Customer::class], function (Builder $customerQuery) use ($user): void {
                    $customerQuery->where('assigned_to', $user->id);
                });
            });

            $overdueQuery->where(function (Builder $q) use ($user): void {
                $q->whereHasMorph('followable', [Lead::class], function (Builder $leadQuery) use ($user): void {
                    $leadQuery->where('assigned_to', $user->id);
                })->orWhereHasMorph('followable', [Customer::class], function (Builder $customerQuery) use ($user): void {
                    $customerQuery->where('assigned_to', $user->id);
                });
            });
        }

        $stats = [
            'total_leads' => Lead::count(),
            'total_customers' => Customer::count(),
            'pending_follow_ups' => $pendingQuery->count(),
            'overdue_follow_ups' => $overdueQuery->count(),
        ];

        return Inertia::render('Dashboard', [
            'upcomingFollowUps' => $upcomingFollowUps,
            'overdueFollowUps' => $overdueFollowUps,
            'stats' => $stats,
        ]);
    }
}
