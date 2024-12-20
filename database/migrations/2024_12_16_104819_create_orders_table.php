<?php

use App\Enums\IamportMethod;
use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->comment('기본키');
            $table->foreignId('user_id')/*->constrained()->onDelete('cascade')*/ ->nullable()->comment('사용자 외래키');
            $table->unsignedBigInteger('guest_id')->nullable()->comment('비회원 아이디');

            $table->enum('status', OrderStatus::values())->default(OrderStatus::ORDER_COMPLETE->value)->comment('주문 상태');
            $table->timestamp('delivery_started_at')->nullable()->comment('배송 시작 일시');
            $table->timestamp('purchase_confirmed_at')->nullable()->comment('주문 확정 일시');


            //주문자정보
            $table->string("buyer_name")->nullable()->comment('주문자 이름');
            $table->string("buyer_email")->nullable()->comment('주문자 이메일');
            $table->string("buyer_phone")->nullable()->comment('주문자 연락처');
            $table->string("buyer_postal_code")->nullable()->comment('주문자 우편번호');
            $table->string("buyer_address")->nullable()->comment('주문자 주소');
            $table->string("buyer_address_detail")->nullable()->comment('주문자 상세주소');

            //배송지 정보
            $table->string('delivery_name')->nullable()->comment('배송지명');
            $table->string('delivery_phone', 20)->nullable()->comment('배송지 연락처');
            $table->string('delivery_postal_code', 10)->nullable()->comment('배송지 우편번호');
            $table->string('delivery_address')->nullable()->comment('배송지 주소');
            $table->string('delivery_address_detail')->nullable()->comment('배송지 상세주소');
            $table->text('delivery_request')->nullable()->comment('배송 요청 사항');
            $table->string('common_entrance_method')->nullable()->comment('공동현관 출입방법');

            //주문금액
            $table->unsignedInteger('total_amount')->comment('주문 총액'); // 주문 총액
            $table->unsignedBigInteger('user_coupon_id')->nullable()->comment('사용자 쿠폰 기본키');
            $table->unsignedInteger('coupon_discount')->nullable()->default(0)->comment('쿠폰 할인액');
            $table->unsignedInteger('use_points')->nullable()->default(0)->comment('적립금 사용액');
            $table->unsignedInteger('delivery_fee')->nullable()->default(0)->comment('배송비');
            $table->unsignedInteger('payment_amount')->default(0)->comment('최종결제액');

            //결제정보
            $table->string("imp_uid")->unique()->nullable()->comment('주문번호 (아임포트)');
            $table->string("merchant_uid")->nullable()->unique()->index()->comment('주문번호 (내부)');
            $table->text("payment_fail_reason")->nullable()->comment('결제실패사유');
            $table->boolean("is_payment_process_success")->default(0)->comment('결제완료처리여부');
            $table->boolean('is_payment_process_record')->default(0)->comment('결제 대기 또는 성공 후 관련내용 기록처리여부');

            // 결제수단
            $table->string("payment_pg")->nullable()->comment('결제 pg ex) html5_inicis');
            $table->enum("payment_method", [IamportMethod::values()])->nullable()->comment('결제수단 ex) card, vbank');

            // 가상계좌
            $table->string("vbank_num")->nullable()->comment('가상계좌 계좌번호');
            $table->string("vbank_name")->nullable()->comment('가상계좌 은행명');
            $table->string("vbank_date")->nullable()->comment('가상계좌 입금기한');

            // 환불계좌
            $table->string("refund_bank_name")->nullable()->comment('환불계좌 은행명');
            $table->string("refund_bank_owner")->nullable()->comment('환불계좌 예금주');
            $table->string("refund_bank_account")->nullable()->comment('환불계좌 계좌번호');
            $table->text("refund_reason")->nullable()->comment('환불사유');

            $table->text("memo")->nullable()->comment('메모');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade')->comment('주문 외래키');
            $table->foreignId('product_id')/*->constrained()->onDelete('cascade')*/->comment('상품 외래키');
            $table->foreignId('product_option_id')->comment('상품 옵션 외래키');
            $table->unsignedInteger('quantity')->default(1)->comment('상품 수량'); // 상품 수량
            $table->unsignedInteger('price')->comment('상품 가격');
            $table->timestamps();
            $table->softDeletes();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
    }
};