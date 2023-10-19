<?php

function formatPrice($price) {
    if (! $price || $price == 0) return "00 руб. 00";
    $floor_price = floor($price);
    $drobnaya = floor(($price - $floor_price)*100);
    if ($drobnaya < 10) $response = $floor_price . " руб. 0" . $drobnaya;
    else $response = $floor_price . " руб. " . $drobnaya;
    return $response;
}
