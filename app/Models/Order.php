<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use App\Models\TransportationFee;

class Order extends Model
{

public $incrementing = false;


protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $userId = Auth::id();         // authenticated user ID
        $date = now()->format('md');       // e.g., 0624 (month and day only)
        $time = now()->format('s') . now()->format('v'); // seconds + milliseconds (e.g., "12" + "123" = "12123")

        $prefix = $userId . $date . $time;

        $orderCode = IdGenerator::generate([
            'table' => 'orders',
            'field' => 'id',
            'length' => strlen($prefix) + 4, // add 4 digits for unique sequence
            'prefix' => $prefix,
            'reset_on_prefix_change' => false,
        ]);

        $model->id = $orderCode;
    });
}
    
    // public $incrementing = false;

    // public static function boot()
    // {
    //     parent::boot();
    //     self::creating(function ($model) {
    //         $model->id = IdGenerator::generate(
    //             [
    //                 'table' => 'orders',
    //                 'length' => 10,
    //                 'prefix' => date('ymd'),
    //                 'reset_on_prefix_change' => true,
    //             ]
    //         );
    //     });
    // }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (!empty($order['destination_address'])) {
                if ($order['destination_address']['id'] < 0) {
                    $address =   Address::create([
                        'user_id' => $order['user_id'],
                        'creator_id' => $order['user_id'],
                    ]);

                    AddressDetail::create([
                        'address_id' => $address->id,
                        'type' => $order['destination_address']['latest']['type'],
                        'status' => 'Created',
                        'name' =>  null,
                        'address_1' =>  strtoupper($order['destination_address']['latest']['address_1']),
                        'address_2'  => strtoupper($order['destination_address']['latest']['address_2']),
                        'latitude'  => $order['destination_address']['latest']['latitude'],
                        'longitude'  => $order['destination_address']['latest']['longitude'],
                        'city'  => $order['destination_address']['latest']['city'],
                        'postcode'  => $order['destination_address']['latest']['postcode'],
                        'state'  => $order['destination_address']['latest']['state'],
                        'creator_id' => $order['user_id'],
                    ]);

                    $order['address_id'] = $address->id;
                }
                unset($order['destination_address']);
            }
        });
    }

    protected $appends = [
        'accepted',
        'available',
        // 'completed', // Removed to prevent N+1 queries - calculate in controller when needed
        // 'ongoing',   // Removed to prevent N+1 queries - calculate in controller when needed
    ];

    protected $casts = [
        'id' => 'integer',
        'price_per_unit' => MoneyCast::class,
        'order_details_sum_cancel' => 'integer',
        'creator_id' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'address_id',
        'created_at',
        'creator_id',
        'price_item_id',
        'price_per_unit',
        'product_id',
        'purchase_id',
        'status',
        'unit',
        'cost_amount',
        'updated_at',
        'user_id',
        'wheel_id',
    ];

    public function getAvailableAttribute()
    {
        $jobs = collect($this->jobs)->pluck('latest')->flatten(2)->toArray();
        $total = $this->latest?->total ?? 0;
        return $total - array_sum(array_column($jobs, 'quantity'));
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where(function ($query) {
            // $query->whereRaw('(SELECT total FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at DESC, id DESC LIMIT 1) = 36');
            // $query->whereRaw('(SELECT SUM(quantity) FROM jobs INNER JOIN job_details ON jobs.id = job_details.job_id WHERE jobs.order_id = orders.id) = 7');
            // $query->whereRaw('(SELECT total FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at ASC, id ASC LIMIT 1) = 15');
            // $query->whereRaw('(SELECT SUM(order_details.cancel) FROM order_details WHERE order_details.order_id = orders.id) = 2');
            // $query->whereRaw('(SELECT SUM(order_delegations.total) FROM order_delegations WHERE order_delegations.order_detail_id = (SELECT id FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at DESC, id DESC LIMIT 1)) = 10');
            $query->whereRaw('((((SELECT total FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at DESC, id DESC LIMIT 1) - (SELECT COALESCE(SUM(job_details.quantity), 0) FROM jobs INNER JOIN job_details ON jobs.id = job_details.job_id WHERE jobs.order_id = orders.id))-((SELECT total FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at ASC, id ASC LIMIT 1) - (SELECT SUM(order_details.cancel) FROM order_details WHERE order_details.order_id = orders.id) - (SELECT COALESCE(SUM(order_delegations.total), 0) FROM order_delegations WHERE order_delegations.order_detail_id = (SELECT id FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at DESC, id DESC LIMIT 1))))> 0)');
        });
    }

    public function getAcceptedAttribute()
    {
        $jobs = collect($this->jobs)->pluck('latest')->flatten(2)->toArray();
        return array_sum(array_column($jobs, 'quantity'));
    }

    public function scopeAccepted(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereRaw('(SELECT COALESCE(SUM(job_details.quantity), 0) FROM jobs INNER JOIN job_details ON jobs.id = job_details.job_id WHERE jobs.order_id = orders.id) > 0');
        });
    }

    public function scopeCancelled(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereRaw('(SELECT SUM(order_details.cancel) FROM order_details WHERE order_details.order_id = orders.id) > 0');
        });
    }

    public function getCompletedAttribute()
    {
        return $this->trips->where('status', 'Completed')->count();
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereRaw("(SELECT COUNT(*) FROM trips WHERE trips.status = 'Completed' AND trips.job_id IN (SELECT id FROM jobs WHERE jobs.order_id = orders.id)) > 0");
        });
    }

    public function scopeHold(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereRaw('(((SELECT total FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at ASC, id ASC LIMIT 1) - (SELECT SUM(order_details.cancel) FROM order_details WHERE order_details.order_id = orders.id) - (SELECT SUM(order_delegations.total) FROM order_delegations WHERE order_delegations.order_detail_id = (SELECT id FROM order_details WHERE order_details.order_id = orders.id ORDER BY created_at DESC, id DESC LIMIT 1)))>0)');
        });
    }

    public function getOngoingAttribute()
    {
        return $this->trips->whereNotIn('status', ['Cancelled', 'Completed', 'Released', 'Terminated'])->count();
    }

    public function scopeOngoing(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereRaw("(SELECT COUNT(*) FROM trips WHERE trips.status NOT IN ('Cancelled','Completed', 'Released', 'Terminated') AND trips.job_id IN (SELECT id FROM jobs WHERE jobs.order_id = orders.id)) > 0");
        });
    }

    /**
     * Scope a query to return app screen data users.
     */
    public function scopeOrderEvent(Builder $query): void
    {
        $query->orderBy('orders.id', 'desc')
            ->with([
                "address.latest.contacts",
                "coin_refunds",
                "jobs.latest",
                'jobs.trips.latest.assignment.driver.user',
                'jobs.trips.latest.assignment.truck',
                "latest.order_contacts",
                "latest.site",
                "material_amount",
                "oldest.order_contacts",
                "oldest.site",
                "order_amounts",
                "product",
                "product.featured_image",
                "purchase",
                "transaction",
                "wheel",
                "transportation_amount",
                'trips.latest.assignment.driver.user',
                'trips.latest.assignment.truck',
                "user.referrer.affiliate.user.company.latest",
            ]);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function business_price_order(): HasOne
    {
        return $this->hasOne(BusinessPriceOrder::class, 'id', 'id');
    }

    public function coin_refunds(): HasMany
    {
        return $this->hasMany(OrderAmount::class, 'order_id', 'id')
            ->where('order_amountable_type', 'coin_refund')
            ->orderBy('created_at', 'ASC');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'creator_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status', 'status');
    }

    /**
     * Check if the order is scheduled for pickup today or in the future.
     *
     * @return bool
     */
    public function isPendingPickup()
    {
        $pickupDate = $this->pickup_date ?: $this->delivery_date;
        return !$pickupDate || now()->startOfDay()->lte($pickupDate);
    }
    
    /**
     * Check if the order pickup is completed (in the past).
     *
     * @return bool
     */
    public function isPickupCompleted()
    {
        $pickupDate = $this->pickup_date ?: $this->delivery_date;
        return $pickupDate && now()->startOfDay()->gt($pickupDate);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function latest(): HasOne
    {
        return $this->hasOne(OrderDetail::class)->latestOfMany();
    }

    public function latest_coin(): MorphOne
    {
        return $this->morphOne(Coin::class, 'coinable')->latestOfMany();
    }

    public function latest_transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable')->latestOfMany();
    }

    public function material_amount(): HasOne
    {
        return $this->hasOne(OrderAmount::class)->ofMany([
            'id' => 'min',
        ], function (Builder $query) {
            $query->where('order_amountable_type', 'material');
        });
    }

    public function oldest(): HasOne
    {
        return $this->hasOne(OrderDetail::class)->oldestOfMany();
    }

    public function oldest_coin(): MorphOne
    {
        return $this->morphOne(Coin::class, 'coinable')->oldestOfMany();
    }

    public function oldest_transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable')->oldestOfMany();
    }

    /**
     * Backwards-compatible alias expected by some controllers/views.
     * Previously some code referenced `orderPayment` — map that to the
     * latest transaction for this order.
     */
    public function orderPayment(): MorphOne
    {
        return $this->latest_transaction();
    }

    public function order_amounts(): HasMany
    {
        return $this->hasMany(OrderAmount::class);
    }

    public function order_details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Backwards-compatible relation name used in some controllers/views
    public function orderDetails(): HasMany
    {
        return $this->order_details();
    }

    public function price_item(): BelongsTo
    {
        return $this->belongsTo(PriceItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function quarry(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
    
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable')->ofMany([
            'id' => 'max',
        ], function (Builder $query) {
            $query->where('amount', '<', 0);
        });
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function transportation_amount(): HasOne
    {
        return $this->hasOne(OrderAmount::class)->ofMany([
            'id' => 'min',
        ], function (Builder $query) {
            // Since we've set up a morphMap in AppServiceProvider, we can simply check for 'transportation'
            // Laravel will automatically resolve this to TransportationFee thanks to our morphMap
            $query->where('order_amountable_type', 'transportation');
        });
    }

    /**
     * Get all of the trips for the project.
     */
    public function trips(): HasManyThrough
    {
        return $this->hasManyThrough(Trip::class, Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wheel(): BelongsTo
    {
        return $this->belongsTo(Wheel::class, 'wheel_id', 'wheel');
    }
}
