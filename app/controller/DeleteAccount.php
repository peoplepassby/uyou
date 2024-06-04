<?php

namespace app\controller;
use think\facade\Db;
use think\facade\Request;

class DeleteAccount
{
    public function deleteaccount()
    {
        // 获取请求参数
        $param = Request::post();

        // 检查参数是否完整
        if (empty($param['CardID']) || empty($param['password'])) {
            return json(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        // 对 CardID 和 password 进行 URL 解码
        $cardID = urldecode($param['CardID']);
        $password = urldecode($param['password']);

        // 检查 CardID 是否为16位字符串
        if (strlen($cardID) !== 16) {
            return json(['status' => 'error', 'message' => 'CardID must be a 16-character string']);
        }

        // 查找账号
        $user = Db::name('account')->where('CardID', $cardID)->find();

        if (!$user) {
            return json(['status' => 'error', 'message' => 'Account does not exist']);
        }

        // 校验密码
        if (md5($password) !== $user['Password']) {
            return json(['status' => 'error', 'message' => 'Invalid password']);
        }

        // 获取OwnerID
        $ownerID = $user['OwnerID'];

        // 删除账户记录
        Db::name('account')->where('CardID', $cardID)->delete();

        // 更新client表中的CardNum字段
        $client = Db::name('client')->where('ID', $ownerID)->find();
        if ($client) {
            $newCardNum = $client['CardNum'] - 1;
            if ($newCardNum > 0) {
                Db::name('client')->where('ID', $ownerID)->update(['CardNum' => $newCardNum]);
            } else {
                Db::name('client')->where('ID', $ownerID)->delete();
            }
        }

        return json(['status' => 'success', 'message' => 'Account deleted successfully']);
    }
    public function deleteuser()
    {

    }

}
