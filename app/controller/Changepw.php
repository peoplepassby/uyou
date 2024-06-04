<?php

namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\facade\Request;

class Changepw extends BaseController
{
    // 修改密码的函数
    public function changePassword()
    {
        // 获取请求参数
        $param = Request::post();

        // 期望的参数列表
        $expectedParams = ['CardID', 'oldPassword', 'newPassword'];

        // 检查所有必需参数是否存在
        foreach ($expectedParams as $paramName) {
            if (!isset($param[$paramName])) {
                return json(['status' => 'error', 'message' => 'Missing required parameter: ' . $paramName]);
            }
        }

        // 检查是否存在额外的未预期的参数
        $unexpectedParams = array_diff(array_keys($param), $expectedParams);
        if (!empty($unexpectedParams)) {
            return json(['status' => 'error', 'message' => 'Unexpected parameters: ' . implode(', ', $unexpectedParams)]);
        }

        // 校验参数格式和类型
        $cardID = $param['CardID'];
        $oldPassword = $param['oldPassword'];
        $newPassword = $param['newPassword'];

        // 检查卡号是否为16位字符串
        if (!is_string($cardID) || strlen($cardID) !== 16) {
            return json(['status' => 'error', 'message' => 'CardID must be a 16-character string']);
        }
        $user = Db::name('account')->where('CardID', $cardID)->find();
        if (!$user) {
            return json(['status' => 'error', 'message' => 'Invalid CardID']);
        }
        // 对新密码进行 MD5 加密
        $encryptedNewPassword = md5($newPassword);
        // 如果数据库中的密码为空，不校验旧密码，更新为新密码
        if (empty($user['Password'])) {
            if (!is_string($newPassword) || !$this->isStrongPassword($newPassword)) {
                return json(['status' => 'error', 'message' => 'New password must be 8-20 characters long, include at least one uppercase letter, one lowercase letter, and one digit']);
            }else{
                $result = Db::name('account')->where('CardID', $cardID)->update(['Password' => $encryptedNewPassword]);
                if ($result) {
                    return json(['status' => 'success', 'message' => 'Password changed successfully']);
                } else {
                    return json(['status' => 'error', 'message' => 'Failed to change password']);
                }
            }
        }
        // 对旧密码进行 MD5 加密
        $encryptedOldPassword = md5($oldPassword);

        // 验证旧密码是否正确
        if ($encryptedOldPassword != $user['Password']) {
            return json(['status' => 'error', 'message' => 'Invalid old password']);
        }
        // 验证新密码是否与旧密码一致
        if (!empty($user['Password'])&&$oldPassword === $newPassword) {
            return json(['status' => 'error', 'message' => 'New password cannot be the same as the old password']);
        }
        // 检查新密码是否为非空字符串并符合强密码要求
        if (!is_string($newPassword) || !$this->isStrongPassword($newPassword)) {
            return json(['status' => 'error', 'message' => 'New password must be 8-20 characters long, include at least one uppercase letter, one lowercase letter, and one digit']);
        }


        // 更新密码
        $result = Db::name('account')->where('CardID', $cardID)->update(['Password' => $encryptedNewPassword]);

        if ($result) {
            return json(['status' => 'success', 'message' => 'Password changed successfully']);
        } else {
            return json(['status' => 'error', 'message' => 'Failed to change password']);
        }
    }
    // 校验强密码的函数
    private function isStrongPassword($password)
    {
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,20}$/';
        return preg_match($pattern, $password);
    }

}