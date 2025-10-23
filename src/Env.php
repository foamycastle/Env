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
                //self::$values[$parts[0]]=$parts[1];
                $_ENV[$parts[0]]=$parts[1];
            }
        }
    }
}