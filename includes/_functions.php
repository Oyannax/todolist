<?php
/**
 * Apply treatment on given array to prevent XSS fault
 *
 * @param array $array
 * @return void
 */
function checkXSS(array &$array): void {
    $array = array_map('strip_tags', $array);
    // foreach ($array as $key => $value) {
    //     $array[$key] = strip_tags($value);
    // }
}