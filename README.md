# ZlibClass
### Сжатие/распаковка данных через zlib для PHP 8.0.0+<br><br>

Советую открыть **`ZlibClass.php`** и почитать описания методов<br><br>

<br><br>
## Пример использования
**`main.php`**:
```php
// подключение ZlibClass
require('ZlibClass/ZlibClass.php');

// создание объекта для сжатия/распаковки данных
$zlib = new ZlibClass(ZLIB_ENCODING_DEFLATE);

// данные не будут сжаты, т.к. применяется метод с проверкой размера данных на профитность
// 123456789 - 9 байт, когда минимальный профитный размер - ZlibClass::PROFIT_DEFLATE (11 байт)
$buffer = $zlib->encode('123456789');

// вывод: 123456789
echo($buffer.PHP_EOL);

// данные не будут распакованы, т.к. применяется метод с проверкой размера данных на профитность
// размер $buffer - 9 байт, когда минимальный профитный размер - ZlibClass::PROFIT_DEFLATE (11 байт)
$buffer = $zlib->decode($buffer);

// вывод: 123456789
echo($buffer.PHP_EOL);
```
