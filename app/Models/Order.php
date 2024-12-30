<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    const MIN_PAYMENT_AMOUNT = 1000;//최소결제금액

    protected $guarded = ['id'];

    protected $casts = [
        'status' => OrderStatus::class, // Enum 캐스팅
    ];

    public static function getCartsData($data, $cartProductOptions)
    {
        return [
            ...$data,
            'total_amount' => $cartProductOptions->sum(function ($cartProductOption) {
                return $cartProductOption->price * $cartProductOption->quantity;
            }),
            'delivery_fee' => $cartProductOptions->pluck('productOption.product.delivery_fee')->filter()->max(),
            'order_products' => $cartProductOptions->map(function ($cartProductOption) use ($data) {
                return self::setOrderProducts([
                    $data['status'],
                    auth()->id() ?? null,
                    $data['guest_id'] ?? null,
                    $cartProductOption->productOption->product_id,
                    $cartProductOption->productOption->id,
                    $cartProductOption->quantity,
                    $cartProductOption->productOption->price,
                    $cartProductOption->productOption->original_price,
                ]);
            })->toArray(),
        ];
    }

    public static function setOrderProducts($orderProduct)
    {
        return [
            'status' => $orderProduct[0], 'user_id' => $orderProduct[1], 'guest_id' => $orderProduct[2],
            'product_id' => $orderProduct[3], 'product_option_id' => $orderProduct[4],
            'quantity' => $orderProduct[5], 'price' => $orderProduct[6],
            'original_price' => $orderProduct[7],
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public static function checkOrderProducts($data)
    {
        $orderProducts = (!empty($data['order_products'])) ? collect($data['order_products']) : $data['orderProducts'];
        $productOptionIds = $orderProducts->pluck('product_option_id');
        $productOptions = ProductOption::with('product')->whereIn('id', $productOptionIds)->get();

        if (empty($productOptions)) {
            abort(422, '상품을 확인해주세요.');
        }

        /**
         * 상품가격(total_amount) 및 상품재고 확인
         */
        $totalAmount = 0;
        foreach ($orderProducts as $e) {
            $productOption = $productOptions->where('product_id', $e['product_id'])->where('id', $e['product_option_id'])->first();
            if ($e['quantity'] > $productOption['stock_quantity']) {
                //abort(422, '상품재고를 확인해주세요.');
                abort(response()->json([
                    'message' => '상품재고를 확인해주세요.',
                    'errors' => ['quantity' => '상품재고를 확인해주세요.'],
                ], 422));
            }
            $totalAmount += $productOption['price'] * $e['quantity'];
        }
        /**
         * 상품가격 확인 total_amount
         */
        /*$totalAmount = $orderProducts->sum(function ($e) use ($productOptions) {
            $productOption = $productOptions->where('product_id', $e['product_id'])->findOrFail($e['product_option_id']);
            return $e['quantity'] * $productOption->price;
        });*/
        if ($data['total_amount'] !== $totalAmount) {
            //abort(422, '상품금액을 확인해주세요.');
            abort(response()->json([
                'message' => '상품금액을 확인해주세요.',
                'errors' => ['total_amount' => '상품금액을 확인해주세요.'],
            ], 422));
        }

        /**
         * 배송비 확인
         */
        {
            $deliveryFee = $productOptions->pluck('product.delivery_fee')->filter()->max();
            if ($deliveryFee > 0) {
                if ($data['delivery_fee'] !== $deliveryFee) abort(422, '배송비를 확인해주세요.');
            }
        }

        return true;
    }

    public function checkOrderAmount($data, $coupon = null)
    {
        self::checkOrderProducts($this->only('total_amount', 'orderProducts', 'delivery_fee'));

        /**
         * 쿠폰 확인
         */
        $couponDiscountAmount = 0;
        if ($data['user_coupon_id'] > 0) {
            if ($data['user_coupon_id'] !== $coupon?->pivot->id) {
                //abort(422, '쿠폰을 확인해주세요.');
                abort(response()->json([
                    'message' => '쿠폰을 확인해주세요.',
                    'errors' => ['user_coupon_id' => '쿠폰을 확인해주세요.'],
                ], 422));
            }
            $couponDiscountAmount = $coupon?->getDiscountAmountByType($this->total_amount + $this->delivery_fee) ?? 0;
            if ($data['coupon_discount_amount'] !== $couponDiscountAmount) {
                //abort(422, '쿠폰 할인금액을 확인해주세요.');
                abort(response()->json([
                    'message' => '쿠폰 할인금액을 확인해주세요.',
                    'errors' => ['coupon_discount_amount' => '쿠폰 할인금액을 확인해주세요.'],
                ], 422));
            }
        }


        /**
         * 적립금 확인
         */
        $usePoint = 0;
        if ($data['use_points'] > 0) {
            if ($data['use_points'] > auth()->user()->points) {
                //abort(422, '적립금 사용액을 확인해주세요.');
                abort(response()->json([
                    'message' => '적립금 사용액을 확인해주세요.',
                    'errors' => ['use_points' => '적립금 사용액을 확인해주세요.'],
                ], 422));
            }
            $usePoint = $data['use_points'];
        }


        /**
         * 최종결제금액 확인
         */
        $paymentAmount = $this->total_amount - $couponDiscountAmount - $usePoint + $this->delivery_fee;
        if ($data['payment_amount'] !== $paymentAmount) {
            //abort(422, '최종 결제금액을 확인해주세요.');
            abort(response()->json([
                'message' => '최종 결제금액을 확인해주세요.',
                'errors' => ['payment_amount' => '최종 결제금액을 확인해주세요.'],
            ], 422));

        }
        if ($data['payment_amount'] < Order::MIN_PAYMENT_AMOUNT) {
            //abort(422, '최소결제금액은 ' . number_format(Order::MIN_PAYMENT_AMOUNT) . '원 입니다.');
            $m = '최소결제금액은 ' . number_format(Order::MIN_PAYMENT_AMOUNT) . '원 입니다.';
            abort(response()->json([
                'message' => $m,
                'errors' => ['payment_amount' => $m],
            ], 422));
        }

        return true;
    }

    public function scopeMine(Builder $query, $request)
    {
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            if (!($request->guest_id)) abort(403, '비회원 아이디가 없습니다.');
            $query->where('guest_id', $request->guest_id);
        }
    }

    public function scopePending(Builder $query)
    {
        $query->where('status', OrderStatus::ORDER_PENDING);
    }

    public function scopeAfterPending(Builder $query)
    {
        $query->whereNot('status', OrderStatus::ORDER_PENDING);
    }

    public function scopeDelivery(Builder $query)
    {
        //if (config('env.app' === 'local')) return; //FORTEST
        $query->where('status', OrderStatus::DELIVERY);//배송중인 경우
    }

    public function scopeDeliveryBefore(Builder $query)
    {
        //if (config('env.app' === 'local')) return; //FORTEST
        $query->whereIn('status', OrderStatus::DELIVERY_BEFORES);//배송중 이전
    }

    /**
     * 주문취소 가능한 상태
     */
    public function scopeCanOrderCancel(Builder $query)
    {
        $query->whereIn('status', OrderStatus::CAN_ORDER_CANCELS);//결제완료, 배송준비중
    }

    public function canOrderCancel()
    {
        return $this->orderProducts->every(fn($e) => in_array($e->status, OrderStatus::CAN_ORDER_CANCELS));
    }



    public function getDepositPoints()
    {
        if (auth()->check()) {
            //주문취소시
            if ($this->status === OrderStatus::CANCELLATION_COMPLETE) {
                return [$this->use_points, '주문취소 적립금 반환'];
            }
        }
        return null;
    }

    public function getWithdrawalPoints()
    {
        if (auth()->check()) {
            if ($this->status === OrderStatus::PAYMENT_COMPLETE && $this->use_points > 0) {
                return [$this->use_points, '주문사용'];
            }

            /*if ($this->status === OrderStatus::CANCELLATION_COMPLETE) {
                return [$this->purchase_deposit_point, '구매 적립금 차감'];
            }*/
        }
        return null;
    }

    public function complete($impUid)
    {
        return DB::transaction(function () use ($impUid) {
            $this->update(["imp_uid" => $impUid, "status" => OrderStatus::PAYMENT_COMPLETE, "payment_completed_at" => now()]);
            $this->syncStatusOrderProducts();

            //쿠폰사용
            if (auth()->check() && $this->user_coupon_id > 0) {
                $coupon = auth()->user()->availableCoupons()->wherePivot('id', $this->user_coupon_id)->first();
                $coupon->pivot->update(['order_id' => $this->id, 'used_at' => now()]);
            }

            //적립금 차감
            if ($this->use_points > 0) {
                auth()->user()->withdrawalPoint($this);
            }

            //재고처리 stock_quantity
            $this->orderProducts->each(function ($e) {
                $e->productOption()->decrement('stock_quantity', $e->quantity);
            });

            return $this;
        });
    }

    public function syncStatusOrderProducts()
    {
        $this->orderProducts()->update(['status' => $this->status]);
    }

    public function cancel()
    {
        $this->update(['status' => OrderStatus::CANCELLATION_COMPLETE, 'payment_canceled_at' => now()]);
        $this->syncStatusOrderProducts();

        //사용한 쿠폰 반환
        if ($this->user_coupon_id > 0) {
            $coupon = auth()->user()->coupons()->wherePivot('id', $this->user_coupon_id)->first();
            $coupon->pivot->update(['order_id' => null, 'used_at' => null]);
        }
        //사용한 적립금 반환
        if ($this->use_points > 0) {
            auth()->user()->depositPoint($this);
        }
        //재고처리 stock_quantity
        $this->orderProducts->each(function ($e) {
            $e->productOption()->increment('stock_quantity', $e->quantity);
        });
    }

}
