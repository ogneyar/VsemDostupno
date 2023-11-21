<?php
// не работает!!!
function sortArrayOfArrays($array, $name) {
    usort($array, function($a, $b) {
        if ($a["${$name}"] > $b["${$name}"]) {
            return 1;
        } elseif ($a["${$name}"] < $b["${$name}"]) {
            return -1;
        }
        return 0;
    });
}
