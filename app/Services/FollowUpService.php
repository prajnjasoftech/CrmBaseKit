<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    /**
     * Add a follow-up to a lead or customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function addFollowUp(Lead|Customer $entity, array $data, User $user): FollowUp
    {
        return DB::transaction(function () use ($entity, $data, $user): FollowUp {
            /** @var FollowUp $followUp */
            $followUp = $entity->followUps()->create([
                'follow_up_date' => $data['follow_up_date'],
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? FollowUp::STATUS_PENDING,
                'created_by' => $user->id,
            ]);

            return $followUp;
        });
    }

    /**
     * Update an existing follow-up.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateFollowUp(FollowUp $followUp, array $data): FollowUp
    {
        return DB::transaction(function () use ($followUp, $data): FollowUp {
            $followUp->update([
                'follow_up_date' => $data['follow_up_date'] ?? $followUp->follow_up_date,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $followUp->notes,
                'status' => $data['status'] ?? $followUp->status,
            ]);

            return $followUp->fresh() ?? $followUp;
        });
    }

    /**
     * Mark a follow-up as completed.
     */
    public function markCompleted(FollowUp $followUp, User $user): FollowUp
    {
        return DB::transaction(function () use ($followUp, $user): FollowUp {
            $followUp->update([
                'status' => FollowUp::STATUS_COMPLETED,
                'completed_by' => $user->id,
                'completed_at' => Carbon::now(),
            ]);

            return $followUp->fresh() ?? $followUp;
        });
    }

    /**
     * Mark a follow-up as cancelled.
     */
    public function markCancelled(FollowUp $followUp): FollowUp
    {
        return DB::transaction(function () use ($followUp): FollowUp {
            $followUp->update([
                'status' => FollowUp::STATUS_CANCELLED,
            ]);

            return $followUp->fresh() ?? $followUp;
        });
    }

    /**
     * Delete a follow-up.
     */
    public function deleteFollowUp(FollowUp $followUp): void
    {
        $followUp->delete();
    }
}
