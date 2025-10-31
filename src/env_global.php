<?php
/**
 * Retrieve and/or set
 * @param string $key
 * @param $default
 * @return string|null
 */
function env(string $key,$default=null):Env{
    $key=strtoupper($key);
    return new Env($key,$default);
}