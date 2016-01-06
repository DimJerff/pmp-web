<?php
/**
 * PHPExcel扩展类
 * Created by PhpStorm.
 * User: GaoJie
 * Date: 14-7-16
 * Time: 下午12:11
 */
class ExcelExtend extends CApplicationComponent{
    // 当前活动的activeSheet
    protected $activeSheet;
    // 当前遍历到的行数
    protected $rowInd;

    /**
     * 下载报表
     * @param PHPExcel $phpExcel
     * @param string $title
     */
    public function download($phpExcel, $title) {
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=".urlencode($title).".xlsx");
        header("Content-Transfer-Encoding: binary ");
        $writer = new PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save("php://output");
        exit;
    }

    /**
     * 设置下载报表的字符串值
     * @param PHPExcel_Worksheet $activeSheet
     * @param $cell
     * @param number $value
     * @internal param string $style
     * @return PHPExcel_Worksheet
     */
    public function excelSetValue($activeSheet, $cell, $value) {
        $activeSheet->getStyle($cell)->getNumberFormat()->setFormatCode('0');
        $activeSheet->SetCellValue($cell, $value);
        return $activeSheet;
    }

    /**
     * 设置下载报表的数字值
     * @param PHPExcel_Worksheet $activeSheet
     * @param $cell
     * @param number $value
     * @param string $format
     * @internal param string $style
     * @return PHPExcel_Worksheet
     */
    public function excelSetNumber($activeSheet, $cell, $value, $format = '#,##0_);[Red](#,##0)') {
        $activeSheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        $activeSheet->SetCellValue($cell, floatval($value));
        return $activeSheet;
    }

    /**
     * 设置下载报表的百分比值
     * @param PHPExcel_Worksheet $activeSheet
     * @param $cell
     * @param number $value
     * @internal param string $style
     * @internal param string $format
     * @return PHPExcel_Worksheet
     */
    public function excelSetPercent($activeSheet, $cell, $value) {
        return $this->excelSetNumber($activeSheet, $cell, $value, '0.00%_);[Red](0.00%)');
    }

    /**
     * 设置下载报表的金钱值
     * @param PHPExcel_Worksheet $activeSheet
     * @param $cell
     * @param number $value
     * @internal param string $style
     * @internal param string $format
     * @return PHPExcel_Worksheet
     */
    public function excelSetMoney($activeSheet, $cell, $value) {
        return $this->excelSetNumber($activeSheet, $cell, $value, '¥#,##0.00_);[Red](¥#,##0.00)');
    }

    /**
     * 设置excel中sheet中指定单元格的居中方式
     * @param $activeSheet 当前处理的sheet对象
     * @param $cell 单元格名称
     * @param string $align 设置align
     * @return mixed
     */
    public function excelSetCenter($activeSheet, $cell, $align = PHPExcel_Style_Alignment::HORIZONTAL_CENTER) {
        $activeSheet->getStyle($cell)->getAlignment()->setHorizontal($align)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        return $activeSheet;
    }

    /**
     * 设置初始化当前sheet
     * @param $activeSheet
     */
    public function initCurrentSheet ($activeSheet) {
        $this->activeSheet = $activeSheet;
        $this->rowInd = 1;
    }

    /**
     * 设置sheet中一行数据
     * @param $item
     * @param $itemNames
     */
    public function setRowSection($item, $itemNames) {
        $ascii = 0;
        ++$this->rowInd;
        foreach($itemNames as $k=>$v) {
            switch ($v) {
                case 'number':
                    $this->excelSetNumber($this->activeSheet, self::cell($ascii, $this->rowInd), $item[$k]);
                    break;
                case 'percent':
                    $this->excelSetPercent($this->activeSheet, self::cell($ascii, $this->rowInd), $item[$k]);
                    break;
                case 'money':
                    $this->excelSetMoney($this->activeSheet, self::cell($ascii, $this->rowInd), $item[$k]);
                    break;
                default:
                    $this->excelSetCenter($this->activeSheet, self::cell($ascii, $this->rowInd))->SetCellValue(self::cell($ascii, $this->rowInd), $item[$k]);
                    break;
            }
            $ascii++;
        }
    }

    /**
     * 初始化excel实例
     * @param $title
     * @param string $author
     * @return PHPExcel
     * @throws PHPExcel_Exception
     */
    public function initExcel($title, $author='limei.com'){
        $objPHPExcel = new PHPExcel();
        // 设置excel的属性
        $objPHPExcel->getProperties()->setCreator("limei.com"); // 创建人
        $objPHPExcel->getProperties()->setLastModifiedBy("limei.com"); // 最后修改人
        $objPHPExcel->getProperties()->setTitle($title); // 标题
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet()->setTitle($title);
        // 初始化当前处理的sheet
        $this->initCurrentSheet($activeSheet);
        return $objPHPExcel;
    }

    /**
     * 设置sheet中第一行的标题
     * @param $arr
     */
    public function setHeadSection($arr) {
        $ascii = 65;
        $i = $this->rowInd;
        foreach ($arr as $k=>$v) {
            $char = chr($ascii);
            $this->excelSetCenter($this->activeSheet, $char.$i)->SetCellValue($char.$i, $v);
            $ascii++;
        }
    }

    /**
     * 设置sheet中具体内容信息
     * @param $records
     * @param $itemNames
     */
    public function setBodySection($records, $itemNames) {
        foreach($records as $item) {
            $this->setRowSection($item, $itemNames);
        }
    }

    /**
     * 设置sheet脚部统计
     * @param $item
     * @param $itemNames
     */
    public function setFootSection($item, $itemNames) {
        $this->setRowSection($item, $itemNames);
    }

    /**
     * 获取行列的单元格
     * @param $col
     * @param $row
     * @return string
     */
    public static function cell($col, $row=''){
        $cell = '';
        if($col > 25){
            $cell .= chr(64+$col / 26);
            $col -= 26;
        }
        $cell .= chr(65+$col);
        return $cell.$row;
    }
}
