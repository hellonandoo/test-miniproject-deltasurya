<?php

namespace App\Services\Discounts;

class PercentageDiscount implements DiscountStrategy
{
    /**
     * Calculate the percentage discount.
     *
     * @param float $price
     * @param object $voucher
     * @return float
     */
    public function calculate(float $price, object $voucher): float
    {
        $discountAmount = ($price * $voucher->discount_value) / 100;

        if (!empty($voucher->max_discount) && $discountAmount > $voucher->max_discount) {
            $discountAmount = (float) $voucher->max_discount;
        }

        // Ensure the discount does not exceed the original price
        return (float) min($discountAmount, $price);
    }
}
