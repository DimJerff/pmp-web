<?php
class Util extends CComponent {
	/* 获取第一个参数 */
	public static function getFirstParamByArray($value = null) {
		if(func_num_args() == 0) $value = $_GET;
		unset($value['_']);
		while(true) {
			if(is_array($value)) {
				if($value) {
					$value = array_shift($value);
					continue;
				}else{
					$value = '';
				}
			}
			$value = trim(strval($value));
			break;
		}
		return $value;
	}
	
	/* 二维数组排序 */
	public static function array_sort($data, $order = array()) {
		if( !$data || !$order ) return $data;
	
		$arglist = array();
		foreach($data as $k => $v)
		foreach($order as $k2 => $v2) {
			if( !isset($arglist[$k2]) ) $arglist[$k2] = array();
			$arglist[$k2][$k] = $v[$k2];
		}
	
		$argstr = '';
		foreach( $order as $k => $v )
			$argstr .= ',$arglist[\''.$k.'\'], SORT_'.$v;
		$argstr{0} = ' ';
	
		eval('array_multisort('.$argstr.',$data);');
		return $data;
	}
	
	const DECDICT = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
	const DECNUM = 64;
	
	/**
	 * 十进制数转换成其它进制
	 * 可以转换成2-70任何进制
	 *
	 * @param integer $num
	 * @param integer $to
	 * @return string
	 */
	public static function decTo($num, $to = self::DECNUM) {
		if ($to == 10 || $to > self::DECNUM || $to < 2) return $num;
		$dict = self::DECDICT;
		$ret = '';
		do {
			$ret = $dict[bcmod($num, $to)] . $ret;
			$num = bcdiv($num, $to);
		} while ($num > 0);
		return $ret;
	}
	
	/**
	 * 其它进制数转换成十进制数
	 * 适用2-70的任何进制
	 *
	 * @param string $num
	 * @param integer $from
	 * @return number
	 */
	public static function decFrom($num, $from = self::DECNUM) {
		if ($from == 10 || $from > self::DECNUM || $from < 2) return $num;
		$num = strval($num);
		$dict = self::DECDICT;
		$len = strlen($num);
		$dec = 0;
		for($i = 0; $i < $len; $i++) {
			$pos = strpos($dict, $num[$i]);
			if ($pos >= $from) continue; // 如果出现非法字符，会忽略掉。比如16进制中出现w、x、y、z等
			$dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
		}
		return $dec;
	}
	
	const URLSIGNKEY = 'df230hf0GHNWEr023_@_)($rureg0oinjdsop}{>\'.\';';
	/**
	 * 网址签名
	 * @param array $params
	 * @param string $key
	 * @param string $isParent
	 * @return string
	 */
	public static function urlSig($params, $key = self::URLSIGNKEY, $isParent = true) {
		if(!$params || !is_array($params)) return $isparent ? '' : $params;

		$params = self::urlSigSort($params);
		if(!$isParent) return $params;
		$params = http_build_query($params);
		return $params . '&_sig=' . substr(md5($params . $key), 8, 16);
	}
	
	/**
	 * 检查网址签名
	 * @param string $params
	 * @param string $uri
	 * @param string $key
	 * @return boolean
	 */
	public static function checkUrlSig($params = null, $uri = null, $key = self::URLSIGNKEY) {
		if(!isset($uri)) $uri = $_SERVER['REQUEST_URI'];
		if(!isset($params)) {
			/* 不用$_SERVER['QUERY_STRING']，避免重写规则导致参数错误 */
			if(($pos = strpos($uri, '?')) === false) return false;
			parse_str(substr($uri, $pos+1), $params);
		}
		$params = self::urlSigSort($params);
		$sig = $params['_sig'];
		/* _用于增加随机字符 */
		unset($params['_sig'], $params['_'], $params['callback']);
		return is_string($sig) && $sig && substr(md5(http_build_query($params) . $key), 8, 16) === $sig;
	}
	
	/**
	 * 网址签名参数排序
	 * @param array $params
	 * @return array
	 */
	private static function urlSigSort($params) {
		$arr = $params;
		$tmpkey = array_keys($arr);
		sort($tmpkey);
		$params = array();
		foreach($tmpkey as $k) {
			$params[$k] = $arr[$k];
			if($params[$k] === '' || $params[$k] === null) unset($params[$k]);
		}
		return $params;
	}
	
	/**
	 * 递归trim
	 * @param array $list
	 * @return mixed
	 */
	public static function recurTrim($list) {
		if(is_scalar($list)) {
			$list = trim($list);
		}else{
			foreach($list as &$item) {
				$item = self::recurTrim($item);
			}
		}
		return $list;
	}
	
	/**
	 * url参数编码
	 */
	public static function urlQueryEscape($url) {
		$data = parse_url($url);
		$pos = strpos($url, '#');
		$fragment = '';
		if($pos !== false) {
			$fragment = substr($url, $pos);
			$url = substr($url, 0, $pos);
		}
		$url = preg_replace_callback('/[^(\x20-\x7F)]+/', __CLASS__.'::urlQueryEscapeCallback', $url).$fragment;
		return $url;
	}

	/**
	 * urlQueryEscape的callback
	 */
	private static function urlQueryEscapeCallback($matches) {
		return urlencode($matches[0]);
	}
	
	/**
	 * 通用标签替换
	 */
	public static function tagReplace($content, $params = array()) {
		if(!is_array($params)) $params = array();
		foreach($params as $k => $v) {
			$content = str_replace('{'.$k.'}', $v, $content);
		}
		return $content;
	}
	
	/**
	 * 执行shell command
	 * @param string $command
	 * @param string $errorMessage
	 * @param mixed $params
	 * @return boolean|multitype:array
	 */
	public static function executeShellCommand($command, $errorMessage = '', $params = array()) {
		/* format shell arguments */
		$argsParams = $params;
		if(!is_array($argsParams)) $argsParams = array();
		foreach($argsParams as &$v) $v = escapeshellarg($v);
		unset($v);

		$command = self::tagReplace($command, $argsParams);
		exec($command, $output, $return);
		if(!empty($return)) {
			$errorMessage = self::tagReplace($errorMessage, $params);
			echo $errorMessage."\n";
			return false;
		}else{
			return array($output, $return);
		}
	}
	
	/**
	 * 是否日期范围
	 * @param string $str
	 * @return number
	 */
	public static function isDateRange($str) {
		return preg_match('/\d{4}\/\d{1,2}\/\d{1,2}\-\d{4}\/\d{1,2}\/\d{1,2}/i', trim($str));
	}
	
	/* or */
	public static function bitwiseOr($values, $fromBase = 16, $toBase = 16) {
		$value = array();
		foreach($values as $str) {
			$newValue = str_split(strrev(self::baseConvert($str, $fromBase, 2)));
			foreach($value as $k => $v) {
				if($v == '1') $newValue[$k] = '1';
				elseif(!$newValue[$k]) $newValue[$k] = '0';
			}
			$value = $newValue;
		}
		$value = self::baseConvert(strrev(implode('', $value)), 2, $toBase);
		return $value;
	}
	
	/* and */
	public static function bitwiseAnd($value1, $value2, $fromBase = 16, $tobase=16) {
		$tmp1 = str_split(strrev(self::baseConvert($value1, $fromBase, 2)));
		$tmp2 = str_split(strrev(self::baseConvert($value2, $tobase, 2)));
		foreach($tmp2 as $k => $v) {
			if($v == '0') continue;
			if($tmp1[$k] != '1') return 0;
		}
		return $value2;
	}
	
	/* convert */
	public static function baseConvert($str, $frombase=10, $tobase=36) {
		$str = trim($str);
		if (intval($frombase) != 10) {
			$len = strlen($str);
			$q = 0;
			for ($i=0; $i<$len; $i++) {
				$r = base_convert($str[$i], $frombase, 10);
				$q = bcadd(bcmul($q, $frombase), $r);
			}
		}
		else $q = $str;
		if (intval($tobase) != 10) {
			$s = '';
			while (bccomp($q, '0', 0) > 0) {
				$r = intval(bcmod($q, $tobase));
				$s = base_convert($r, 10, $tobase) . $s;
				$q = bcdiv($q, $tobase, 0);
			}
		}
		else $s = $q;
	
		return $s;
	}
	
	public static function split($str, $split = ',') {
		$result = array();
		$str = trim($str);
		if($str !== '') {
			$list = preg_split('/[\s'.preg_quote($split, '/').']+/i', $str);
			foreach($list as $v) {
				$v = trim($v);
				if($v !== '') $result[] = $v; 
			}
		}
		return $result;
	}

    /**
     * 二维中过滤下标第一个字符为下划线的键值对
     * @param $data 需要过滤的数组
     * @return mixed
     */
    public static function filterUnderlineKey($data, $pos=0) {
        foreach ($data as $k=>$v) {
            if (strpos($k, '_') === $pos) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    // 处理力列表数据总计
    public static function listAmount($records) {
        // 处理统计数据
        $amount = array();
        if ($records) {
            $amount['adslotCount'] = 0;
            $amount['cost'] = 0;
            $amount['bidRequest'] = 0;
            $amount['impressions'] = 0;
            $amount['clicks'] = 0;

            $amount['fillingr'] = 0;
            $amount['ctr'] = 0;
            $amount['ecpm'] = 0;
            $amount['ecpc'] = 0;
            foreach ($records as $v) {
                $amount['adslotCount'] += empty($v['adslotCount']) ? 0 : $v['adslotCount'];
                $amount['cost']        += $v['cost'];
                $amount['bidRequest']  += $v['bidRequest'];
                $amount['impressions']  += $v['impressions'];
                $amount['clicks']  += $v['clicks'];
            }
            if ($amount['bidRequest']) {
                $amount['fillingr'] =  round($amount['impressions']/$amount['bidRequest'] * 100, 2);
            }
            if ($amount['impressions']) {
                $amount['ctr'] =  round($amount['clicks']/$amount['impressions'] * 100, 2);
            }
            if ($amount['impressions']) {
                $amount['ecpm'] =  $amount['cost']/$amount['impressions'] / 1000;
            }
            if ($amount['clicks']) {
                $amount['ecpc'] =  $amount['cost']/$amount['clicks'] / 1000000;
            }
        }
        return $amount;
    }
}