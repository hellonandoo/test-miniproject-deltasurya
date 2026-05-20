<?php

namespace App\Services\Discounts;

use InvalidArgumentException;

class DiscountFactory
{
    /**
     * Create the appropriate discount strategy based on the discount type.
     *
     * @param string $discountType
     * @return DiscountStrategy
     * @throws InvalidArgumentException
     */
    public static function make(string $discountType): DiscountStrategy
    {
        return match ($discountType) {
            'percentage' => new PercentageDiscount(),
            'fixed'      => new FixedDiscount(),
            default      => throw new InvalidArgumentException("Tipe diskon tidak dikenali: {$discountType}")
        };
    }
}
