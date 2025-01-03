<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class CouponResource extends JsonResource
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
            ...$this->only(['id', 'name', 'type', 'discount_amount', 'minimum_purchase_amount', 'discount_rate', 'usage_limit_amount', 'valid_days', 'issued_until']),
            'human_issued_until' => Carbon::parse($this->issued_until)->diff(Carbon::now())->format('%d일 %h시간 %i분'), //Carbon::parse($this->issued_until)->diffForHumans()
            'is_downloaded' => $this->is_downloaded,
        ];

        //*
        if (config('scribe.response_file')) {
            $comments = [
                'human_issued_until' => '',
                'is_downloaded' => '다운로드 여부'
            ];
            return getScribeResponseFile($return, 'coupons', $comments);
        }
        //*/
        return $return;
        //*/

    }
}
