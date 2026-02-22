<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property EntityType $entity_type
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_CHURNED = 'churned';

    protected $fillable = [
        'name',
        'entity_type',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'status',
        'notes',
        'converted_from_lead_id',
        'assigned_to',
        'business_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Customer $customer): void {
            DB::transaction(function () use ($customer): void {
                $customer->contactPeople()->delete();
                $customer->followUps()->delete();
                $customer->projects()->delete();
            });
        });
    }

    /**
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_CHURNED => 'Churned',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'converted_from_lead_id');
    }

    /**
     * @return MorphMany<ContactPerson, $this>
     */
    public function contactPeople(): MorphMany
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    /**
     * @return MorphMany<FollowUp, $this>
     */
    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'followable');
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function wasConvertedFromLead(): bool
    {
        return $this->converted_from_lead_id !== null;
    }

    public function isIndividual(): bool
    {
        return $this->entity_type === EntityType::Individual;
    }

    public function isBusiness(): bool
    {
        return $this->entity_type === EntityType::Business;
    }
}
