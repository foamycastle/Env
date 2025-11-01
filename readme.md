# The Env Class
A script that adds helpful functionality to working with the $_ENV superglobal. The `env` function lives in the global scope.

## Example Uses
```dotenv
# Example .env file
API_KEY=api_key_826dba01
USER=username
PASS=jhd83:sadj:sj89
```
```php
//Load the .env file from the current script's path
Env::Load();
$key=env('user')

//Load the .env file from a discreet path
Env::Load('/path/to/.env')
$key=env('password');
```