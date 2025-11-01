<?php
use Foamycastle\Utilities\Str;
use Foamycastle\Utilities\Arr;
use Foamycastle\Utilities\Path;
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
    public const array COMMENT_CHARS=[';','#','*','/','[',']'];
    /**
     * The value of the requested $_ENV key
     * @var mixed|string[]
     */
    private mixed $value;

    /**
     * The data type of the output
     * @var string
     */
    private string $inputType;

    /**
     * If the output format is an array, this is property will dictate if the array will be flattened on output
     * @var bool
     */
    private bool $flatten=false;

    /**
     * @param string $key
     * @param mixed $default
     * @param string $delimiter
     */
    public function __construct(
        private          string $key,
        private readonly mixed  $default,
        private readonly string $delimiter=';',
    )
    {
        //if the $key argument is not prefixed with the value of the ENV_PREFIX constant, make it so
        if(!Str::Left($this->key,ENV_PREFIX)){
            $this->key=ENV_PREFIX.$this->key;
        }

        /*  check to see if that particular key is set in the $_ENV superglobal
        *   If it is not set, return the default value if given.
         *  Set the value property with the value contained in the $_ENV superglobal
        */
        if(isset($_ENV[$this->key])) {
            $this->value=$_ENV[$this->key];
        }else{
            $this->value=$this->default;
        }

        /*
         * Search the value for the delimiter. If the delimiter is found,
         * assume that the value is meant to be an array
         */
        if(str_contains($this->value, $this->delimiter)){
            $this->value=explode($this->delimiter, $this->value);
        }

        //finally, the set the data type property
        $this->inputType=gettype($this->value);

    }

    /**
     * Standard magic method for returning a string.  If the $_ENV value is an array, a flattened array is returned as a string
     * @return string
     */
    public function __toString(): string
    {
       if($this->inputType=='array'){
           $output=$this->value;
           if($this->flatten) {
               Arr::Flatten($output);
           }
           return join($this->delimiter, $output);
       }
       return $this->value;
    }

    /**
     * Return an array if the value of the ENV variable has been read as an array
     * @return array|string[]
     */
    public function __toArray(): array
    {
        if($this->inputType=='string'){
            $this->flatten=false;
            return [$this->key=>$this->value];
        }
        $output=[];
        if($this->flatten) {
            Arr::Flatten([$this->key=>$this->value],$output);
        }
        $this->flatten=false;
        return $output;
    }

    public function flatten():self
    {
        $this->flatten=true;
        return $this;
    }

    /**
     * Load variables from a file into the $_ENV superglobal
     * @param string $path the path to the file. this method will look in the $path argument and will look for an already-defined constant called 'APP_PATH'
     * @param bool $clear if True, the variables in the $_ENV superglobal that begin with the ENV_PREFIX value will be cleared.
     * @return void
     * @throws Exception
     */
    public static function Load(string $path="", bool $clear=false):void
    {
        if(empty($path)){
            $path = self::chooseEnvPath();
        }
        if(!empty($path)){
            $path = Path::Prepare($path);
            if($path===''){
                throw new Exception("Invalid path");
            }
        }

        //cycle through each line in the file
        foreach((file($path,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) as $line){

            //test the line for comment chars
            $firstChar=substr($line, 0, 1);
            if(in_array($firstChar, self::COMMENT_CHARS)){
                //if detected, ignore the line
                continue;
            }
            $parts=explode('=',$line,2) ?: [];

            //MOST LIKELY A KEY/VALUE PAIR
            if(count($parts)==2){
                $parts[0]=strtoupper($parts[0]);
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
     * Returns a constant value that specifies a path to an .env file
     * @return string
     */
    private static function chooseEnvPath():string
    {
        //path argument is empty.  look for an 'APP_PATH' or 'FOAMYCST_APP_PATH' global that may contain a path to an env
        return match(true){
            defined(ENV_PREFIX.'APP_PATH')=>    constant(ENV_PREFIX.'APP_PATH'),
            defined(ENV_PREFIX.'ENV_PATH')=>    constant(ENV_PREFIX.'ENV_PATH'),
            defined('APP_PATH')=>               constant('APP_PATH'),
            defined('ENV_PATH')=>               constant('ENV_PATH'),
            defined('ENV_FILENAME')=>           constant('ENV_FILENAME'),
            default=>''
        };
    }
}