<?php

if (! function_exists('formatIDR')) {
    function formatIDR($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}
