<?php
/**
 * 格式化打印函数
 */
function p($arr){
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

/**
 * 调试项目中的数组到文件中
 * @param $arr 需要打印数据
 * @param string $str 别名
 */
function fpc($arr,$str=""){
    if($str!=""){$str .= "------";}
    if (is_object($arr) && isset($arr->attributes) && is_array($arr->attributes)) { // 检测是否是Yii数据模型对象
        $val = json_encode($arr->attributes);
    } elseif (is_array($arr)){
        $val = json_encode($arr);
    } else {
        $val = $arr;
    }
    $runtimePath = './protected/runtime/';
    file_put_contents($runtimePath."_Trace.txt",$str.date("Y-m-d H:i:s")."\n".$val."\n\n",FILE_APPEND);
}
?>