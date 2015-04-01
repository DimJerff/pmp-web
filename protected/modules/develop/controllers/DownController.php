<?php
/**
 * 下载控制器
 * User: limei
 * Date: 15-3-19
 * Time: 下午5:40
 */
class DownController extends Controller {
    // 不检测权限
    public $noCheckPermission = TRUE;

    // 下载sdk
    public function actionSdk($adslotId) {
        $data['adslotId'] = $adslotId;
        // 查询该广告位的信息
        $adslot = MediaAdslot::model()->getAdslotById($adslotId);


        $data['title'] = "集成SDK";
        $data['footer'] = '<a href="javascript: void(0);" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">完成</a>';
        $body = $this->smartyRender(array(
            'adslot' => $adslot,
        ), null, true);
        $data['body'] = $body;

        $this->rspJSON($data);
    }
}