<?php


/**
 * Return the value of an environment variable, optionally with a default value is the specified key is not present
 * @param string $key
 * @param string $default
 * @return string
 */
function env(string $key, string $default=""):string
{
    return $_ENV[$key] ?? $default;
}