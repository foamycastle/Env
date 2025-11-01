<?php
/**
 * Retrieve and/or set an ENV
 * @param string $key
 * @param null $default
 * @return Env
 */
function env(string $key,$default=null):Env{
    $key=strtoupper($key);
    return new Env($key,$default);
}