<?php

namespace App\Enums;

enum TaskFrequency: string
{
    case Daily = 'daily';
    case Weekdays = 'weekdays';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    /**
     * Get the human readable label of the frequency.
     */
    public function label(): string
    {
        return match ($this) {
            self::Daily => __('Daily'),
            self::Weekdays => __('Weekdays'),
            self::Weekly => __('Weekly'),
            self::Monthly => __('Monthly'),
        };
    }
}
