<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        $return = [
            ...$this->only([
                'id', 'buyer_name', 'buyer_email', 'buyer_phone', 'buyer_postal_code', 'buyer_address', 'buyer_address_detail',
                'delivery_name', 'delivery_phone', 'delivery_postal_code', 'delivery_address', 'delivery_address_detail', 'delivery_request', 'common_entrance_method',
                'total_amount', 'user_coupon_id', 'coupon_discount', 'use_points', 'delivery_fee', 'payment_amount',
                'imp_uid', 'merchant_uid',
                'payment_pg', 'payment_method',
                'created_at', 'delivery_started_at', 'purchase_confirmed_at',
            ]),
            //'status' => OrderStatus::from($this->status)->label(),
            'status' => OrderStatus::from($this->status->value)->label(),
            'orderProducts' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
        ];

        //*
        if (config('scribe.response_file')) {
            $comments = [
            ];
            return getScribeResponseFile($return, 'orders', $comments);
        }
        //*/
        return $return;
        //*/
    }
}
