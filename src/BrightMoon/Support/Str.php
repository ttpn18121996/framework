<?php

namespace BrightMoon\Support;

class Str
{
    /**
     * Lưu trữ các chuỗi snake case đã chuyển.
     *
     * @var array
     */
    private static $snakeCache = [];

    /**
     * Lưu trữ các chuỗi studly case đã chuyển.
     *
     * @var array
     */
    private static $studlyCache = [];

    /**
     * Trả về phần còn lại của một chuỗi sau một giá trị nhất định.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Trả lại phần còn lại của một chuỗi sau lần xuất hiện cuối cùng của một giá trị nhất định.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function afterLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Trả về phần còn lại của một chuỗi trước một giá trị nhất định.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    /**
     * Lấy phần còn lại của một chuỗi trước khi một giá trị nhất định xuất hiện cuối cùng.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return static::substr($subject, 0, $pos);
    }

    /**
     * Lấy đoạn giữa của 2 chuỗi đầu và cuối được chỉ định.
     *
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * @return string
     */
    public static function between($subject, $from, $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Chuyển chuỗi thành chuỗi lưng con lạc đà (camelCase).
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        $result = static::title($value);
        $result = ucwords(str_replace(['-', '/', ' ', '_'], '', $result));
        $result = static::lower(substr($result, 0, -strlen($result) + 1)).substr($result, 1);

        return $result;
    }

    /**
     * Đếm số lần ký tự xuất hiện trong chuỗi.
     *
     * @param  $string
     * @return array
     */
    public static function countChar($string, $insensitive = true)
    {
        $result = [];

        if (empty($string)) {
            return $result;
        }

        $chars = str_split($string);

        $left = 0;
        $right = count($chars) - 1;
        do {
            $chars[$left] = $insensitive ? self::lower($chars[$left]) : $chars[$left];
            $chars[$right] = $insensitive ? self::lower($chars[$right]) : $chars[$right];
            if ($chars[$left] == $chars[$right]) {
                $char = $chars[$left];
                $result[$char] = isset($result[$char]) ? $result[$char] += 2 : 2;
            } else {
                $result[$chars[$left]] = isset($result[$chars[$left]]) ? ++$result[$chars[$left]] : 1;
                $result[$chars[$right]] = isset($result[$chars[$right]]) ? ++$result[$chars[$right]] : 1;
            }

            $left++;
            $right--;
        } while ($left < $right);

        return $result;
    }

    /**
     * Kiểm tra sự tồn tại của 1 chuỗi trong 1 chuỗi cho trước.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return string
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra sự tồn tại của các chuỗi trong 1 chuỗi cho trước.
     *
     * @param  string  $haystack
     * @param  string[]  $needles
     * @return string
     */
    public static function containsAll($haystack, array $needles)
    {
        foreach ((array) $needles as $needle) {
            if (! static::contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    /**
     * So sánh chuỗi với biểu thức mẫu (pattern).
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = is_array($pattern) ? $pattern : (array) $pattern;

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            // Nếu giá trị đã cho là khớp chính xác, tất nhiên ta có thể trả về giá trị
            // đúng ngay từ đầu. Mặt khác, ta sẽ dịch các dấu sao và thực hiện
            // khớp mẫu thực tế với hai chuỗi để xem chúng có khớp không.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Dấu hoa thị được dịch thành các ký tự đại diện biểu thức chính quy bằng 0
            // hoặc nhiều hơn để thuận tiện cho việc kiểm tra xem các chuỗi có
            // bắt đầu bằng mẫu đã cho không, chẳng hạn như "library/*", giúp kiểm tra
            // bất kỳ chuỗi nào thuận tiện.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Giới hạng số lượng ký tự chuỗi.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Chuyển toàn bộ chuỗi thành in chữ thường.
     *
     * @param  string $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Khử dấu tiếng Việt.
     *
     * @param  string $string
     * @return string
     */
    public static function noneUnicode($string)
    {
        $arr = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ|Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ồ|ố|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($arr as $key => $value) {
            $a = explode("|", $value);
            $string = static::lower(str_replace($a, $key, $string));
        }

        return $string;
    }

    /**
     * Khởi tạo đối tượng Stringable để xử lý chuỗi.
     *
     * @param  string  $value
     */
    public static function of($value)
    {
        return new Stringable($value);
    }

    /**
     * Chèn thêm cả hai đầu của một chuỗi bằng một chuỗi khác.
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padBoth($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_BOTH);
    }

    /**
     * Chèn thêm vào đầu của một chuỗi bằng một chuỗi khác.
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padLeft($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_LEFT);
    }

    /**
     * Chèn thêm vào đuôi của một chuỗi bằng một chuỗi khác.
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padRight($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_RIGHT);
    }

    /**
     * Chuyển kiểu Class[@]method thành Class và method.
     *
     * @param  string  $callback
     * @param  string|null  $default
     * @return array<int, string|null>
     */
    public static function parseCallback($callback, $default = null)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Tạo 1 chuỗi ngẫu nhiên.
     *
     * @param  int  $length
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Tìm và thay thế chuỗi.
     *
     * @param  string  $value
     * @param  array|string  $search
     * @param  array|string  $replace
     * @return string
     */
    public static function replace($value, $search, $replace = '')
    {
        return str_replace($search, $replace, $value);
    }

    /**
     * Chuyển chuỗi dạng slug.
     * chuyen-chuoi-dang-slug
     *
     * @param  string  $value
     * @param  string  $separator
     * @return string
     */
    public static function slug($value, $separator = '-')
    {
        // Chuyển toàn bộ dấu / hoặc _ thành -
        $flip = $separator === '-' ? '_' : '-';

        $value = preg_replace('!['.preg_quote($flip).']+!u', $separator, $value);

        // Thay dấu @ thành từ 'at'
        $value = str_replace('@', $separator.'at'.$separator, $value);

        // Xóa tất cả các ký tự không phải là dấu -, chữ, số hoặc khoảng trắng
        $value = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', static::lower($value));

        // Thay tất cả các dấu - đứng sát nhau và khoảng trắng thành dấu - đơn
        $value = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $value);

        return trim($value, $separator);
    }

    /**
     * Chuyển chuỗi dạng snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Chuyển một chuỗi thành chuỗi in hoa các chữ cái đầu sau mỗi khoảng trắng.
     * bright moon - BrightMoon
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Cắt chuỗi tính từ một vị trí cụ thể và cắt một lượng ký tự.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Chuyển toàn bộ chuỗi thành chuỗi in hoa các chữ cái đầu.
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Chuyển ký tự đầu tiên một chuỗi thành in hoa.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Chuyển toàn bộ chuỗi thành in hoa.
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
