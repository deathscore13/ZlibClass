<?php

/**
 * ZlibClass
 * 
 * Сжатие/распаковка данных через zlib для PHP 8.0.0+
 * https://github.com/deathscore13/ZlibClass
 */

class ZlibClass
{
    /**
     * Минимальный профитный размер при сжатии
     */
    public const PROFIT_RAW = 5;
    public const PROFIT_DEFLATE = 11;
    public const PROFIT_GZIP = 23;

    private int $encoding;
    private int $profit;

    private int $level;
    private int $memory;
    private int $window;
    private int $strategy;
    private int $dictionary;

    /**
     * Конструктор
     * 
     * @param int $encoding             Алгоритм сжатия (одна из констант ZLIB_ENCODING_*)
     * @param int $level                Уровень сжатия в диапазоне -1..9
     * @param int $memory               Уровень памяти сжатия в диапазоне 1..9
     * @param int $window               Размер окна zlib (логарифмический) в диапазоне 8..15. zlib изменяет размер окна с 8 на 9,
     *                                  а с zlib 1.2.8 будет выдавать предупреждение, если запрашивается размер окна равный 8 для
     *                                  ZLIB_ENCODING_RAW или ZLIB_ENCODING_GZIP
     * @param int $strategy             Одна из констант: ZLIB_FILTERED, ZLIB_HUFFMAN_ONLY, ZLIB_RLE, ZLIB_FIXED или ZLIB_DEFAULT_STRATEGY
     * @param int $dictionary           Строка или массив строк текущего словаря (по умолчанию предустановленного словаря нет)
     */
    public function __construct(int $encoding = ZLIB_ENCODING_RAW, int $level = 9, int $memory = 8, int $window = 15,
        int $strategy = ZLIB_DEFAULT_STRATEGY, int $dictionary = '')
    {
        switch ($encoding)
        {
            case ZLIB_ENCODING_RAW:
            {
                $this->profit = PROFIT_RAW;
                break;
            }
            case ZLIB_ENCODING_DEFLATE:
            {
                $this->profit = PROFIT_DEFLATE;
                break;
            }
            case ZLIB_ENCODING_GZIP:
            {
                $this->profit = PROFIT_GZIP;
                break;
            }
            default:
            {
                throw new Exception('Unknown encoding type');
            }
        }
        $this->encoding = $encoding;

        if ($level < -1 || 9 < $level)
            throw new Exception('Invalid level value');
        $this->level = $level;

        if ($memory < 1 || 9 < $memory)
            throw new Exception('Invalid memory value');
        $this->memory = $memory;

        if ($window < 8 || 15 < $window)
            throw new Exception('Invalid window value');
        $this->window = $window;

        if ($strategy !== ZLIB_DEFAULT_STRATEGY && $strategy !== ZLIB_FILTERED && $strategy !== ZLIB_HUFFMAN_ONLY &&
            $strategy !== ZLIB_RLE && $strategy !== ZLIB_FIXED)
            throw new Exception('Invalid strategy value');
        $this->strategy = $strategy;

        $this->dictionary = $dictionary;
    }

    /**
     * Сжатие данных
     * 
     * @param string $buffer            Данные для сжатия
     * 
     * @return string|false             Сжатые данные или false в случае ошибки
     */
    public function encodeEx(string $buffer): string|false
    {
        $hndl = deflate_init($this->encoding, [
            'level' => $this->level,
            'memory' => $this->memory,
            'window' => $this->window,
            'strategy' => $this->strategy,
            'dictionary' => $this->dictionary
        ]);

        if ($hndl === false)
            return false;

        return deflate_add($hndl, $buffer, ZLIB_FINISH);
    }

    /**
     * Сжатие данных с проверкой на профитность
     * 
     * @param string $buffer            Данные для сжатия
     * 
     * @return string|false             Сжатые данные или false в случае ошибки
     */
    public function encode(string $buffer): string|false
    {
        return $this->isProfit($buffer) ? $this->encodeEx($buffer) : $buffer;
    }

    /**
     * Распаковка данных
     * 
     * @param string $buffer            Данные для распаковки
     * 
     * @return string|false             Оригинальные данные или false в случае ошибки
     */
    public function decodeEx(string $buffer): string|false
    {
        $hndl = inflate_init($this->encoding, [
            'level' => $this->level,
            'memory' => $this->memory,
            'window' => $this->window,
            'strategy' => $this->strategy,
            'dictionary' => $this->dictionary
        ]);

        if ($hndl === false)
            return false;
        
        $res = @inflate_add($hndl, $buffer, ZLIB_FINISH);
        if ($res === false || inflate_get_read_len($hndl) !== strlen($buffer))
            return false;
        
        return $res;
    }

    /**
     * Распаковка данных с проверкой на профитность
     * 
     * @param string $buffer            Данные для распаковки
     * 
     * @return string                   Оригинальные данные
     */
    public function decode(string $buffer): string
    {
        return ($this->isProfit($buffer) && ($ret = $this->decodeEx($buffer)) !== false) ? $ret : $buffer;
    }

    /**
     * Проверяет профитный размер данных (если меньше - можно не запаковывать/распаковывать)
     * 
     * @param string $buffer            Данные для проверки
     * 
     * @param bool                      true если выгодно, false если нет
     */
    public function isProfit(string $buffer): bool
    {
        $i = -1;
        while (isset($buffer[++$i]) && $i < $this->profit)
            continue;
        
        return $this->profit <= $i;
    }
}
