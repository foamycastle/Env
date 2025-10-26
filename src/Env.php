<?php

namespace Foamycastle\Utilities;

class Env
{
    public static function Load(string $path="", bool $clear=false):void
    {

        //path not found or accessible
        if ($path==""||!file_exists($path)) {
            return;
        }

        //cycle through each line in the file
        foreach((file(realpath($path),FILE_IGNORE_NEW_LINES) ?: []) as $line){
            $parts=explode('=',$line,2) ?: [];

            //MOST LIKELY A KEY/VALUE PAIR
            if(count($parts)==2){
                //TEST FOR BOOLEAN
                if (strtolower($parts[1])=='true' || strtolower($parts[1])=='false') {
                    $_ENV[$parts[0]] = strtolower($parts[1])=='true';
                    continue;
                }

                //TEST FOR NUMBERS
                if(is_numeric($parts[0])){
                    if(is_double($parts[0])){
                        $_ENV[$parts[0]] = floatval($parts[0]);
                        continue;
                    }
                    $_ENV[$parts[0]] = intval($parts[0]);
                    continue;
                }

                $_ENV[$parts[0]]=$parts[1];
            }
        }
    }

    /**
     * Set an environment variable. Many variables may set from an array. If both arrays contain an equal number of elements,
     *  each key will match with each value in the same position in each array. If the arrays contain an unequal number of elements,
     *  only the elements with corresponding value in each array will be added and other values will be ignored.
     * @param string|array<int,string> $key
     * @param string|array<int,string> $value
     * @return void
     */
    public static function Set(string|array $key, array|string|null $value=null):void
    {
        if(is_string($key)) {
            if(is_array($value) && !empty($value)){
                $_ENV[$key] = join("; ", array_values($value)[0]);
            }elseif(is_string($value)){
                $_ENV[$key] = $value;
            }
            return;
        }
        if (is_array($key)) {
            if (is_array($value) && (array_is_list($key) && array_is_list($value))) {
                if (reset($key) !== false && reset($value) !== false) {
                    do {
                        $_ENV[current($key)] = current($value) ?? '';
                    } while (next($key) !== false);
                }
            }
        }
    }
}