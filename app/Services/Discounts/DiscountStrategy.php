<?php

namespace App\Services\Discounts;

interface DiscountStrategy
{
    /**
     * Calculate the discount amount based on the given price and voucher.
     *
     * @param float $price
     * @param object $voucher
     * @return float
     */
    public function calculate(float $price, object $voucher): float;
}
