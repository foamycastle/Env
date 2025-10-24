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

    public static function Set(string $key, $value):void
    {
        $_ENV[$key] = $value;
    }
}