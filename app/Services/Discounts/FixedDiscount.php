<?php

namespace App\Services\Discounts;

class FixedDiscount implements DiscountStrategy
{
    /**
     * Calculate the fixed discount.
     *
     * @param float $price
     * @param object $voucher
     * @return float
     */
    public function calculate(float $price, object $voucher): float
    {
        $discountAmount = (float) $voucher->discount_value;

        // Ensure the discount does not exceed the original price
        return (float) min($discountAmount, $price);
    }
}
