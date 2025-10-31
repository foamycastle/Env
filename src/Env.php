<?php
use Foamycastle\Utilities\Str;
use Foamycastle\Utilities\Arr;
/*
 * This library will only create, read, update, delete env variable prefixed with the following value
 */
defined('ENV_PREFIX')||define('ENV_PREFIX', 'FOAMYCST_');

/*
 * Define the default filename for the .env file
 */
defined('ENV_FILENAME')||define('ENV_FILENAME', '.env');

/*
 * When using the clear function, the following variable names will be excluded from the clear
 * if the variable include the ENV_PREFIX.  Again, any variable name not beginning with the prefix
 * will be ignored.
 */
defined('ENV_EXCLUDED_FROM_CLEAR')||define('ENV_EXCLUDED_FROM_CLEAR', [
    'APP_PATH'
]);

class Env
{
    public const int OUTPUT_ARRAY=0;
    public const int OUTPUT_STRING=1;
    private mixed $value;
    private string $inputType;
    private bool $flatten=false;
    /**
     * @param scalar|array $value
     */
    public function __construct(
        private readonly string $key,
        private readonly mixed  $default,
        private readonly int    $outputType=self::OUTPUT_ARRAY,
        private string          $delimiter=';',
    )
    {
        if(!isset($_ENV[$this->key])) {
            $this->value=$this->default ?? '';
        }
        $this->inputType=gettype($this->value);
        if($outputType==self::OUTPUT_ARRAY && $delimiter==''){
            $this->delimiter=';';
        }
    }


    public function __toString(): string
    {
        if($this->outputType == self::OUTPUT_STRING){
            switch($this->inputType){
                case 'string':  return (string)$this->value;
                case 'array':   return join($this->delimiter, $this->value);
            }
        }
        if($this->outputType == self::OUTPUT_ARRAY){
            return '';
        }
        return (string)$this->value ?? '';
    }

    public function __toArray(): array
    {
        if($this->outputType == self::OUTPUT_ARRAY){
            switch($this->inputType){
                case 'string':  return explode($this->delimiter, $this->value);
                case 'array':
                    if($this->flatten){
                        $output=clone $this->value;
                        Arr::flatten($output);
                        $this->flatten=false;
                        return $output;
                    }
            }
        }
        return [];
    }

    public function flatten():self
    {
        $this->flatten=true;
        return $this;
    }

    /**
     * Load variables from a file into the $_ENV superglobal
     * @param string $path  the path to the file. this method will look in the $path argument and will look for an already-defined constant called 'APP_PATH'
     * @param bool $clear   if True, the variables in the $_ENV superglobal that begin with the ENV_PREFIX value will be cleared.
     * @return void
     */
    public static function Load(string $path="", bool $clear=false):void
    {
        if(!empty($path)){
            if(basename($path)==""){
                $path=dirname($path).DIRECTORY_SEPARATOR.ENV_FILENAME;
            }
        }
        if(Str::Right($path,ENV_FILENAME,Str::CMP_CIS)){
            $path=realpath($path);
            if(!file_exists($path)){
                //the path provided is no good
                return;
            }
        }else{
            if(file_exists($path)){
                if(trim(basename($path))=="" || !Str::Right(basename($path),ENV_FILENAME, Str::CMP_CIS)){
                    $path=realpath(dirname($path).DIRECTORY_SEPARATOR.ENV_FILENAME);
                }
            }
        }

        //cycle through each line in the file
        foreach((file($path,FILE_IGNORE_NEW_LINES) ?: []) as $line){
            $parts=explode('=',$line,2) ?: [];

            //MOST LIKELY A KEY/VALUE PAIR
            if(count($parts)==2){
                //TEST FOR BOOLEAN
                if (strtolower($parts[1])=='true' || strtolower($parts[1])=='false') {
                    $_ENV[ENV_PREFIX.$parts[0]] = strtolower($parts[1])=='true';
                    continue;
                }

                //TEST FOR NUMBERS
                if(is_numeric($parts[0])){
                    if(is_double($parts[0])){
                        $_ENV[ENV_PREFIX.$parts[0]] = floatval($parts[0]);
                        continue;
                    }
                    $_ENV[ENV_PREFIX.$parts[0]] = intval($parts[0]);
                    continue;
                }

                $_ENV[ENV_PREFIX.$parts[0]]=$parts[1];
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
                $_ENV[ENV_PREFIX.$key] = join("; ", array_values($value)[0]);
            }elseif(is_string($value)){
                $_ENV[ENV_PREFIX.$key] = $value;
            }
            return;
        }
        if (is_array($key)) {
            if (is_array($value) && (array_is_list($key) && array_is_list($value))) {
                if (reset($key) !== false && reset($value) !== false) {
                    do {
                        $_ENV[ENV_PREFIX.current($key)] = current($value) ?? '';
                    } while (next($key) !== false);
                }
            }
        }
    }
}