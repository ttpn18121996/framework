<?php

namespace BrightMoon\Support;

use \ReflectionClass;

class Dumper
{
    const ARRAY_TYPE = 'array';
    const BOOL_TYPE = 'boolean';
    const FLOAT_DOUBLE_TYPE = 'double';
    const INTEGER_TYPE = 'integer';
    const OBJECT_TYPE = 'object';
    const STRING_TYPE = 'string';
    const NULL_TYPE = 'NULL';
    const BASE_TYPE = [
        self::BOOL_TYPE, self::FLOAT_DOUBLE_TYPE, self::INTEGER_TYPE, self::STRING_TYPE, self::NULL_TYPE
    ];

    /**
     * @var string
     */
    private $content;

    /**
     * Xử lý phân tích dữ liệu đưa vào.
     *
     * @param  mixed  $arg
     * @return void
     */
    public function dump($arg)
    {
        if (!defined("DUMP_DEBUG_SCRIPT")) {
            define("DUMP_DEBUG_SCRIPT", true);

            echo $this->getStyle(config('constant.dump.theme'));
            echo $this->getScript();
        }
        
        $type = gettype($arg);
        switch ($type) {
            case self::STRING_TYPE:
                $this->content = $this->getContentTypeBase($type, strlen($arg), '"'.htmlentities($arg).'"');
                break;
            case self::INTEGER_TYPE:
                $this->content = $this->getContentTypeBase($type, strlen((string) $arg), $arg);
                break;
            case self::FLOAT_DOUBLE_TYPE:
                $this->content = $this->getContentTypeBase($type, strlen((string) $arg), $arg);
                break;
            case self::BOOL_TYPE:
                $this->content = $this->getContentTypeBase($type, strlen($arg), $arg);
                break;
            case self::NULL_TYPE:
                $this->content = $this->getContentTypeBase($type, 0, 'null');
                break;
            case self::ARRAY_TYPE:
                $this->content = $this->getContentTypeArray($arg);
                break;
            case self::OBJECT_TYPE:
                $this->content = $this->getContentTypeObject($arg);
                break;

            default:
                $this->content = $this->getContentTypeBase($type, 0, 'null');
        }

        echo $this->content.'<div class="mt-2 mb-2"><hr/></div>';
    }

    /**
     * Lấy nội dung dữ liệu dạng đối tượng.
     *
     * @param  object  $obj
     * @param  int  $level
     * @return string
     */
    public function getContentTypeObject($obj, $level = 0)
    {
        $type = get_class($obj);
        $id = spl_object_id($obj);
        if ($type != 'stdClass') {
            $ref = new ReflectionClass($type);
            $temp = [];
            $props = $ref->getProperties();
            foreach ($props as $prop) {
                $prop->setAccessible(true);

                $temp[(($prop->isPublic())
                    ? '[+] '
                    : (($prop->isPrivate()) ? '[-] ' : '[#] ')).$prop->name] = $prop->getValue($obj);
            }
            $obj = $temp;
        } else {
            $obj = (array) $obj;
        }

        return $this->getContentTypeArray($obj, $level, $type, $id);
    }

    /**
     * Lấy nội dung dữ liệu dạng dữ liệu thuần.
     *
     * @param  string  $type
     * @param  int  $length
     * @param  string  $value
     * @param  string  $key
     * @return string
     */
    private function getContentTypeBase($type, $length, $value, $key = null, $charPoint = ' => ')
    {
        $content = '<span class="dumper-type">'.$type.'('.$length.') </span>
                <span class="dumper-value-'.$type.'">'.$value.'</span>';

        if ($type == self::NULL_TYPE) {
            $content = '<span class="dumper-type">'.strtolower($type).'</span>';
        } elseif ($type == self::BOOL_TYPE) {
            $content = '<span class="dumper-type">'.$type.'('.$length.') </span>
                <span class="dumper-value-'.$type.'">'.($value ? 'true' : 'false').'</span>';
        }

        if (is_null($key)) {
            return '<div id="id-'.rand().'">'.$content.'</div>';
        }

        return '<span class="dumper-key">'.$key.$charPoint.'</span>'.$content;
    }

    /**
     * Lấy nội dung dữ liệu dạng mảng.
     *
     * @param  array  $data
     * @param  int  $lavel
     * @param  string  $type
     * @return string
     */
    private function getContentTypeArray(array $data, $level = 0, $type = 'array', $objectId = 0)
    {
        $length = count($data);
        $charPoint = $type != 'array' ? ': ' : ' => ';
        $blank = '';
        for ($i = 0; $i < $level; $i++) {
            $blank .= '&nbsp;&nbsp;';
        }

        $rs = '';

        if ($level == 0) {
            $rs .= '<div id="id-'.rand().'-'.$level.'">
            '.$blank;
        }
        
        $rs .= '<span class="dumper-'.($type != 'array' ? 'value-obj' : 'type').' pointer">'.$type.
            ($type == 'array' ? '('.$length.')' : '&nbsp;{...}[#'.$objectId.']').'&nbsp;'.
            ($length > 0
                ? '<span class="dumper-toggle" data-char="'.($level == 0 ? '&#9207;' : '&#9204;').'"></span>'
                : '').'</span>
            <div class="dumper-value-arr-obj '.($level == 0 ? '' : 'dnone').'">';
        if ($length) {
            $level++;
            foreach ($data as $key => $value) {
                if ($value instanceof $type) {
                    $rs .= '<div>';
                    for ($i = 0; $i < $level; $i++) {
                        $rs .= '&nbsp;&nbsp;';
                    }

                    $rs .= '<span class="dumper-key">'.$key.': </span>
                    <span class="dumper-value-obj">'.$type.' {...}</span></div>';
                } else {
                    $rs .= $this->getContentItemArray($key, $value, $level, $charPoint);
                }
            }
        }

        $rs .= $level == 0 ? '</div></div>' : '</div>';

        return $rs;
    }

    /**
     * Lấy nội dung dự liệu các phần tử mảng.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $level
     * @return string
     */
    private function getContentItemArray($key, $value, $level, $charPoint = ' => ')
    {
        $type = gettype($value);
        $blank = '';
        for ($i = 0; $i < $level; $i++) {
            $blank .= '&nbsp;&nbsp;';
        }

        $rs = '<div id="id-'.rand().'-'.$level.'">'.$blank;
        if (in_array($type, self::BASE_TYPE)) {
            $value = is_null($value) ? 'null' : $value;
            $value = $type == self::STRING_TYPE ? '"'.htmlentities($value).'"' : htmlentities($value);
            $rs .= $this->getContentTypeBase($type, $type == self::STRING_TYPE ? strlen($value) - 2 : strlen($value), $value, $key, $charPoint);
        } elseif ($type == self::ARRAY_TYPE) {
            $rs .= '<span class="dumper-key">'.$key.$charPoint.'</span>';
            $rs .= $this->getContentTypeArray($value, $level);
        } elseif ($type == self::OBJECT_TYPE) {
            $rs .= '<span class="dumper-key">'.$key.$charPoint.'</span>';
            $rs .= $this->getContentTypeObject($value, $level);
        }

        return $rs.'</div>';
    }

    /**
     * Thiết lập nhúng css.
     *
     * @return string
     */
    private function getStyle($theme = 'dark')
    {
        if ($theme == 'dark') {
            return '<style>
            *{padding: 0;margin: 0;box-sizing: border-box;font-family: Monaco, Consolas, monospace;font-size: 12px;font-weight: 400;line-height: 1.5rem;color:#fff;}body{background: #0e0e0e;padding: 0.5rem;}
            .dnone{display: none !important;}.dblock{display: block !important;}.pointer{cursor: pointer;}
            .mb-2{margin-bottom: 0.5rem;}.mt-2{margin-top: 0.5rem;}.dumper-type{color: #e87c08;user-select: none;}
            .dumper-value-string{color: #15dd15;}.dumper-value-integer{color: #007bff;}.dumper-value-obj{color: #ffc107;}
            .dumper-value-double{color: #ff11ff;}.dumper-value-boolean{color: #dc3545;}.dumper-key{color: #fefefe;}
            .dumper-toggle{color:#fefefe;user-select: none;}.dumper-toggle::before{content: attr(data-char);}.dumper-value-arr-obj{transition: all 0.5s ease-in-out;}
            </style>';
        }

        return '<style>
            *{padding: 0;margin: 0;box-sizing: border-box;font-family: Monaco, Consolas, monospace;font-size: 12px;font-weight: bold;line-height: 1.5rem;color:#000;}body{background: #fefefe;padding: 0.5rem;}
            .dnone{display: none !important;}.dblock{display: block !important;}.pointer{cursor: pointer;}
            .mb-2{margin-bottom: 0.5rem;}.mt-2{margin-top: 0.5rem;}.dumper-type{color: #e87c08;user-select: none;}
            .dumper-value-string{color: #28a745;}.dumper-value-integer{color: #007bff;}.dumper-value-obj{color: #e6b218;}
            .dumper-value-double{color: #ff11ff;}.dumper-value-boolean{color: #dc3545;}.dumper-key{color: #0e0e0e;}
            .dumper-toggle{color:#0e0e0e;user-select: none;}.dumper-toggle::before{content: attr(data-char);}.dumper-value-arr-obj{transition: all 0.5s ease-in-out;}
            </style>';
    }

    /**
     * Thiết lập nhúng css và javascript.
     *
     * @return string
     */
    private function getScript()
    {
        // Arial, Helvetica, sans-serif
        return '<script async defer>
            document.addEventListener("DOMContentLoaded", function () {
                const pointerElem = document.querySelectorAll(".pointer");
                pointerElem.forEach(function (elem) {
                    elem.addEventListener("click", function () {
                        const child = this.querySelector(".dumper-toggle");
                        if (child) {
                            const input = child.dataset.char;
                            const dumperValue = this.parentNode.querySelector(".dumper-value-arr-obj");
                            const output = input.charCodeAt(0);
                            if (output == "9207") {
                                this.children[0].dataset.char = String.fromCharCode(9204);
                                dumperValue.classList.add("dnone");
                            } else {
                                this.children[0].dataset.char = String.fromCharCode(9207);
                                dumperValue.classList.remove("dnone");
                            }
                        }
                    });
                });
            }, false);
        </script>';
    }
}
