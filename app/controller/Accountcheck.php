<?php

namespace app\controller;
use think\Controller;
use think\facade\Db;
use think\facade\Request;
class Accountcheck
{
    public function validateCredentials()
    {
        // 获取请求参数
        $param = Request::post();

        // 期望的参数列表
        $expectedParams = ['CardID', 'password'];

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
        $password = $param['password'];

        // 检查卡号是否为16位字符串
        if (!is_string($cardID) || strlen($cardID) !== 16) {
            return json(['status' => 'error', 'message' => 'CardID must be a 16-character string']);
        }

        // 检查密码是否为字符串
        if (!is_string($password)) {
            return json(['status' => 'error', 'message' => 'Password must be a string']);
        }

        // 验证账号
        $user = Db::name('account')->where('CardID', $cardID)->find();

        if ($user) {
            // 如果数据库中的密码为空，不校验密码
            if (empty($user['Password'])) {
                return json(['status' => 'success', 'message' => 'Login successful (no password required)']);
            }

            // 对传入的密码进行 MD5 加密
            $encryptedPassword = md5($password);

            // 验证密码
            if ($encryptedPassword == $user['Password']) {
                return json(['status' => 'success', 'message' => 'Login successful']);
            } else {
                return json(['status' => 'error', 'message' => 'Invalid account or password']);
            }
        } else {
            return json(['status' => 'error', 'message' => 'Invalid account or password']);
        }
    }
}
