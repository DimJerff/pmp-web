<?php

/**
 * 异步获取报表数据
 * Class AjaxReportController
 */
class AjaxReportController extends Controller
{
	/* 不检测权限 */
	public $noCheckPermission = TRUE;
	
	public $defaultCompanyID = 0;

    /**
     * 获取应用图表数据
     * @param string $time
     * @param int $mediaId
     */
	public function actionMediaChartData($time='', $mediaId=0){
        $this->_chartData('ReportMedia', $time, $mediaId);
	}


    /**
     * 获取广告位图表数据
     * @param string $time
     * @param int $adslotId
     */
    public function actionAdslotChartData($time='', $adslotId=0) {
        $this->_chartData('ReportAdslot', $time, $adslotId);
    }

    /**
     * 获取图表的数据
     * @param $modelPart
     * @param string $time
     * @param int $id
     */
    protected function _chartData($modelPart, $time='', $id=0) {
        // 获取当前用户的信息
        $yii = Yii::app();
        $user = $yii->user;
        $userState = $user->getRecord();

        // 获取当前用户的默认公司id
        $this->defaultCompanyID = $defaultCompanyId = $userState->defaultCompanyID;

        // 对时间进行判断处理
        $timeArr = explode('_', $time);

        $timeInterval = $timeArr[1] - $timeArr[0];
        if ($timeInterval > (86400 * 30 *2) ) {
            $timeType = 'Monthly';
        } else if ($timeInterval > 86400) {
            $timeType = "Daily";
        } else {
            $timeType = "Hourly";
        }

        // 实例化报表数据获取数据
        //$reportName = "ReportMediaHourly";
        $reportName = 'ReportDeal' . $timeType;
        $reportModel = $reportName::model();
        switch ($modelPart) {
            case "ReportMedia":
                $dbChartData = $reportModel->getReportByCidAndTimeOfMid($defaultCompanyId, $timeArr, $id);
                break;
            case "ReportAdslot":
                $dbChartData = $reportModel->getReportByCidAndTimeOfAid($defaultCompanyId, $timeArr, $id);
                break;
        }
        // 对从数据库取出来的数据进行整理成图标所需数据
        $chartData = $this->_dbData2ChartData($dbChartData, $timeArr);

        $data = array(
            'chartData' => $chartData,
        );

        $this->rspJSON($data);
    }

    /**
     * 对从数据库取出来的数据进行整理成图标所需数据
     * @param $data
     * @param $timeArr
     * @return array
     */
    private function _dbData2ChartData($data, $timeArr) {
        $arr = array();
        foreach ($data as $v) {
            $arr[$v['dateTime']] = $v;
        }
        $interval = $timeArr[1] - $timeArr[0];
        if ($interval > 86400 * 2 * 30) {
            $timeType = 1; // 月
        } else if ($interval > 86400) {
            $timeType = 2; // 天
        } else {
            $timeType = 3; // 时
        }

        $chartData = array(
            'categories' => array(),
            'bidRequest' => array(),
            'impressions' => array(),
            'clicks' => array(),
            'fillingr' => array(),
            'ctr' => array(),
        );
        for ($startTime = $timeArr[0]; $startTime <= $timeArr[1];) {
            switch ($timeType) {
                case 1:
                    $chartData['categories'][] = date('Y/m', $startTime);
                    break;
                case 2:
                    $chartData['categories'][] = date('m/d', $startTime);
                    break;
                case 3:
                    $chartData['categories'][] = date('m/d H:i', $startTime);
                    break;
            }

            if (isset($arr[$startTime]) && !empty($arr[$startTime])) {
                $chartData['bidRequest'][] = (int)$arr[$startTime]['bidRequest'];
                $chartData['impressions'][] = (int)$arr[$startTime]['impressions'];
                $chartData['clicks'][] = (int)$arr[$startTime]['clicks'];
                $chartData['fillingr'][] = (float)$arr[$startTime]['fillingr'];
                $chartData['ctr'][] = (float)$arr[$startTime]['ctr'];
            } else {
                $chartData['bidRequest'][] = 0;
                $chartData['impressions'][] = 0;
                $chartData['clicks'][] = 0;
                $chartData['fillingr'][] = 0;
                $chartData['ctr'][] = 0;
            }

            switch ($timeType) {
                case 1:
                    $startTime = strtotime(date('Y-m-01', $startTime) ." +1 month");
                    break;
                case 2:
                    $startTime += 86400;
                    break;
                case 3:
                    $startTime += 3600;
                    break;
            }
        }

        return $chartData;
    }
}
