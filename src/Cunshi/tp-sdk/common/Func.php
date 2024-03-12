<?php

namespace extend;

class Func
{
    /**
     * 创建文件目录并赋予权限
     *
     * @return void
     */
    public static function mkdir_chmod($dir, $permissions = 0777)
    {
        if (!is_dir($dir)) {
            mkdir($dir, $permissions, true);
            chmod($dir, $permissions);
        }
    }

    public static function remote_file_exists($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $result = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $httpStatus == 200;
    }

    /**
     * 判断字符串是否为合法json数据
     *
     * @param string $string
     * @return boolean
     */
    public static function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 删除一个目录
     *
     * @param string $dir
     * @return boolean
     */
    public static function remove_dir($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                    self::remove_dir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    @unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }

        @rmdir($dir);
        return true;
    }

    /**
     * 二维数组根据某个字段排序
     *
     * @param array $array 要排序的数组
     * @param string $keys 要排序的键字段
     * @param string $sort 排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    public static function array_sort($array, $keys, $sort = SORT_DESC)
    {
        $keys_value = [];

        foreach ($array as $k => $v) {
            $keys_value[$k] = $v[$keys];
        }

        array_multisort($keys_value, $sort, $array);
        return $array;
    }

    /**
     * array to xml
     *
     * @param $arr
     * @return string
     */
    public static function array_to_xml($arr)
    {
        $xml = "<xml>";

        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . self::array_to_xml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }

        $xml .= "</xml>";
        return $xml;
    }

    /**
     * xml to array
     *
     * @param $xml
     * @return mixed
     */
    public static function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true);  // 禁止引用外部xml实体
        $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return json_decode(json_encode($xml_string), true);
    }

    /**
     * 格式化参数
     * [array to key1=val1&key2=val2]
     *
     * @param  $params
     * @param  $url_encode
     * @return string
     */
    public static function format_params($params, $url_encode)
    {
        if (!$params) return '';

        $param_url = '';
        ksort($params);

        foreach ($params as $k => $v) {
            if ($url_encode) {
                $v = urlencode($v);
            }

            $param_url .= $k . '=' . $v . '&';
        }

        if (strlen($param_url) > 0) {
            $param_url = substr($param_url, 0, strlen($param_url) - 1);
        }

        return $param_url;
    }

    /**
     * 获取时间差值
     *
     * @param string $started_at
     * @param string $ended_at
     * @param string $unit
     * @param integer $decimal
     * @return false
     */
    public static function get_dates_duration($started_at, $ended_at, $unit = 'd', $decimal = 2)
    {
        $started_timestamp = strtotime($started_at);
        $ended_timestamp = strtotime($ended_at);

        $per = null;

        if ($unit == 'd') {
            $per = 1 * 60 * 60 * 24;
        } elseif ($unit == 'h') {
            $per = 1 * 60 * 60;
        } elseif ($unit == 'm') {
            $per = 1 * 60;
        } elseif ($unit == 's') {
            $per = 1;
        } else {
            return false;
        }
        return round(($ended_timestamp - $started_timestamp) / $per, $decimal);
    }

    /**
     * 判断两组时间段是否有交集
     *
     * @param a组时间段开始 $astart
     * @param a组时间段结束 $aend
     * @param b组时间段开始 $bstart
     * @param b组时间段结束 $bend
     * @return boolean
     */
    public static function judge_timetable_conflict($astart, $aend, $bstart, $bend)
    {
        if ($bstart - $astart > 0) {
            if ($bstart - $aend <= 0) {
                return true;
            }
        } else {
            if ($bend - $astart > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 验证身份证号是否合法
     *
     * @param string $identification_num 身份证号
     * @return boolean
     */
    public static function verify_identification_num($identification_num)
    {
        if (strlen($identification_num) == 18) {
            $identification_num_base = substr($identification_num, 0, 17);

            if (verify_identification_num_code($identification_num_base) == strtoupper(substr($identification_num, 17, 1))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     *
     * @param string $identification_num_base 身份证号前17位
     * @return mixed
     */
    public static function verify_identification_num_code($identification_num_base)
    {
        if (strlen($identification_num_base) != 17) {
            return false;
        }

        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);  // 加权因子
        $verify_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');  // 校验码对应值
        $sum = 0;

        for ($i = 0; $i < strlen($identification_num_base); $i++) {
            if (!is_numeric(substr($identification_num_base, $i, 1))) {
                return false;
            }

            $sum += substr($identification_num_base, $i, 1) * $factor[$i];
        }

        $mod = $sum % 11;
        $verify_number = $verify_list[$mod];

        return $verify_number;
    }

    /**
     * 转换日期格式
     *
     * @param string $str_date 日期字符串
     * @param string $str_format 要转换的日期字符串格式
     * @return string 返回转换日期字符串结果
     */
    public static function str_convert_date_format($str_date = '', $str_format = 'Y-m-d')
    {
        if (!$str_date) {
            return null;
        }

        $formats = [
            'y年m月d日',
            'Y年m月d日',
            'm月d日y年',
            'm月d日Y年',
            'y/m/d',
            'Y/m/d',
            'y.m.d',
            'Y.m.d',
            'y-m-d',
            'Y-m-d'
        ];

        foreach ($formats as $format) {
            if ($date_time = \DateTime::createFromFormat($format, $str_date)) {
                return $date_time->format($str_format);
            }
        }

        return $str_date;
    }

    /**
     * 验证日期格式是否为所需格式
     *
     * @param string $str_date 日期字符串
     * @param string $str_format 要验证的日期格式
     * @return boolean 返回验证结果
     */
    public static function verify_date_format($str_date = '', $str_format = 'Y-m-d')
    {
        if (!$str_date) {
            return false;
        }

        return \DateTime::createFromFormat($str_format, $str_date) ? true : false;
    }

    /**
     * 去除字符串中所有空格
     * @param string $str
     * @return string
     */
    public static function remove_blank_space($str = '')
    {
        if (!$str) {
            return '';
        }

        return str_replace(
            ["　", " ", "\n", "\r", "\t"],
            ["", "", "", "", ""],
            $str
        );
    }

    /**
     * 中文字符串替代
     * @param string $str 目标字符串
     * @param string $replace_str 替换字符串
     * @param integer $left_pos 左侧开始替换位置
     * @param integer $left_len 左侧替换长度
     * @param integer $right_pos 右侧开始替换位置
     * @param integer $right_len 右侧替换长度
     * @return string
     */
    public static function cn_substr_replace(
        $str,
        $replace_str = '**',
        $left_pos,
        $left_len,
        $right_pos,
        $right_len
    )
    {
        if (!$str) return '';
        $len = mb_strlen($str, 'utf-8');

        if ($len >= 6) {
            $str_left = mb_substr($str, $left_pos, $left_len, 'utf-8');
            $str_right = mb_substr($str, $right_pos, $right_len, 'utf-8');
        } else {
            $str_left = $str_right = '';
        }

        return $str_left . $replace_str . $str_right;
    }

    /**
     * 保存网络文件到服务器生成临时文件
     *
     * @param string $url 文件网络链接
     * @param string $content 文件内容
     * @param string $fname 文件名称
     * @return array
     */
    public static function save_tmp_file($url, $content, $fname = '')
    {
        $base_path = date('Ymd');
        $tmps_dir = \think\facade\App::getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'tmps' . DIRECTORY_SEPARATOR . $base_path;

        self::mkdir_chmod($tmps_dir);
        $fname = $fname ?: basename($url);                                 //返回路径中的文件名部分
        $ext = '.' . strtolower(pathinfo(basename($url))['extension']);  //把扩展名转换成小写
        $fpath = $tmps_dir . DIRECTORY_SEPARATOR . md5($url) . $ext;

        $fp = fopen($fpath, 'w+');
        fwrite($fp, $content);
        fclose($fp);

        return [
            'path' => $fpath,
            'name' => $fname,
            'url' => $url
        ];
    }

    /**
     * 将下划线字符串转为小驼峰字符串
     *
     * @param string $str
     * @return  string
     * @example little_red_ass_monkey return 'littleRedAssMonkey'
     */
    public static function underline_to_littlecamelcase($str)
    {
        if (!$str) return '';

        $res = '';
        $arr = explode('_', $str);

        foreach ($arr as $i => $item) {
            if ($i > 0) {
                $c = strtoupper(substr($item, 0, 1));
                $res .= substr_replace($item, $c, 0, 1);
            } else {
                $res .= $item;
            }
        }

        return $res;
    }

    /**
     * 获取url参数数组
     *
     * @param string $url url字符串
     * @param string $key 参数键名
     * @return mixed
     */
    public static function get_url_params($url, $key = '')
    {
        if (!(parse_url($url)['query'] ?? '')) return '';

        $params = explode('&', parse_url($url)['query'] ?? '');
        $res = [];

        foreach ($params as $param) {
            $item = explode('=', $param);
            $res[$item[0]] = $item[1];
        }

        return $key ? ($res[$key] ?? '') : $res;
    }
}