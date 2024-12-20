<?php

namespace App\Enums;

enum OrderStatus: string
{
    case ORDER_PENDING	           =	'order_pending';
    case ORDER_COMPLETE	           =	'order_complete';
    case PAYMENT_PENDING	       =	'payment_pending';
    case PAYMENT_COMPLETE	       =	'payment_complete';
    case DELIVERY_PREPARING	       =	'delivery_preparing';
    case DELIVERY	               =	'delivery';
    //case DELIVERY_COMPLETE	       =	'delivery_complete';
    case PURCHASE_CONFIRM	       =	'purchase_confirm';
    case CANCELLATION_REQUESTED	   =	'cancellation_requested';
    case CANCELLATION_COMPLETE	   =	'cancellation_complete';
    case RETURN_REQUESTED	       =	'return_requested';
    case RETURN_COMPLETE	       =	'return_complete';
    case EXCHANGE_REQUESTED	       =	'exchange_requested';
    case EXCHANGE_COMPLETE	       =	'exchange_complete';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_PENDING => '주문접수',
            self::ORDER_COMPLETE => '주문완료',
            self::PAYMENT_PENDING => '결제대기중',
            self::PAYMENT_COMPLETE => '결제완료',
            self::DELIVERY_PREPARING => '배송준비중',
            self::DELIVERY => '배송중',
            //self::DELIVERY_COMPLETE => '배송완료',
            self::PURCHASE_CONFIRM => '구매확정',
            self::CANCELLATION_REQUESTED => '취소요청',
            self::CANCELLATION_COMPLETE => '취소완료',
            self::RETURN_REQUESTED => '반품요청',
            self::RETURN_COMPLETE => '반품완료',
            self::EXCHANGE_REQUESTED => '교환요청',
            self::EXCHANGE_COMPLETE => '교환완료'
        };
    }

    public static function getItems(): array
    {
        $results = [];
        foreach (self::cases() as $case) {
            $results[] = ['value' => $case->value, 'text' => $case->label()];
        }
        return $results;
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

}