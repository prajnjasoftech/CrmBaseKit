<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property EntityType $entity_type
 */
class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_PROPOSAL = 'proposal';

    public const STATUS_NEGOTIATION = 'negotiation';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_REFERRAL = 'referral';

    public const SOURCE_ADVERTISEMENT = 'advertisement';

    public const SOURCE_COLD_CALL = 'cold_call';

    public const SOURCE_SOCIAL_MEDIA = 'social_media';

    public const SOURCE_OTHER = 'other';

    protected $fillable = [
        'name',
        'entity_type',
        'email',
        'phone',
        'company',
        'source',
        'status',
        'notes',
        'assigned_to',
        'business_id',
        'service_id',
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
        static::deleting(function (Lead $lead): void {
            DB::transaction(function () use ($lead): void {
                $lead->contactPeople()->delete();
                $lead->followUps()->delete();
            });
        });
    }

    /**
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_PROPOSAL => 'Proposal',
            self::STATUS_NEGOTIATION => 'Negotiation',
            self::STATUS_WON => 'Won',
            self::STATUS_LOST => 'Lost',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_ADVERTISEMENT => 'Advertisement',
            self::SOURCE_COLD_CALL => 'Cold Call',
            self::SOURCE_SOCIAL_MEDIA => 'Social Media',
            self::SOURCE_OTHER => 'Other',
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
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return HasOne<Customer, $this>
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'converted_from_lead_id');
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

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_WON && $this->customer()->exists();
    }

    public function canBeConverted(): bool
    {
        return $this->status === self::STATUS_WON && ! $this->isConverted();
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
