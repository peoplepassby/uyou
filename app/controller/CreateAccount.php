<?php

namespace app\controller;
use app\BaseController;
use think\Controller;
use think\facade\Db;
use think\facade\Request;

class CreateAccount extends BaseController
{
    private $prefix = '959673'; // 固定前缀，可以根据需要修改

    public function createaccount()
    {
        $param = Request::post();

        // 检查参数是否完整
        if (!isset($param['Name']) || !isset($param['Sex']) || empty($param['Id']) || empty($param['Phone']) || empty($param['Address'])) {
            return json(['status' => 'error', 'message' => 'Missing required parameters']);
        }

        $cardID = $this->generateUniqueCardID();
        $password = isset($param['password']) ? urldecode($param['password']) : '';

        // 检查密码是否为强密码
        if (!empty($password) && !$this->isStrongPassword($password)) {
            return json(['status' => 'error', 'message' => 'Password must be 8-20 characters long, include at least one uppercase letter, one lowercase letter, and one digit']);
        }

        // 对传入的密码进行 MD5 加密
        $encryptedPassword = empty($password) ? '' : md5($password);

        // 检查 Name 是否为非空字符串
        if (!is_string($param['Name']) || empty(trim($param['Name']))) {
            return json(['status' => 'error', 'message' => 'Invalid name']);
        }

        // 检查 Sex 是否为 0 或 1
        if (!in_array($param['Sex'], [0, 1, '0', '1'], true)) {
            return json(['status' => 'error', 'message' => 'Invalid sex']);
        }

        // 检查 Id 是否为18位字符串
        if (!is_string($param['Id']) || strlen($param['Id']) !== 18) {
            return json(['status' => 'error', 'message' => 'Id must be an 18-character string']);
        }

        // 检查 Phone 是否为11位数字串
        if (!is_numeric($param['Phone']) || strlen($param['Phone']) !== 11) {
            return json(['status' => 'error', 'message' => 'Phone must be an 11-digit number']);
        }

        // 检查 Address 是否为非空字符串
        if (!is_string($param['Address']) || empty(trim($param['Address']))) {
            return json(['status' => 'error', 'message' => 'Invalid address']);
        }

        // 检查客户信息是否已存在
        $clientId = $param['Id'];
        $existingClient = Db::name('client')->where('Id', $clientId)->find();

        if ($existingClient) {
            // 如果客户名称与现有名称不匹配，则返回错误
            if ($existingClient['Name'] !== $param['Name']) {
                return json(['status' => 'error', 'message' => 'Invalid Id or Name']);
            }
        }

        // 设置当前时间为开户日期
        $openDate = date('Y-m-d H:i:s');

        // 创建新卡
        $accountData = [
            'CardID' => $cardID,
            'Password' => $encryptedPassword,
            'FixedDeposit' => 0,
            'Term' => 0,
            'Balance' => 0.0,
            'OpenDate' => $openDate,
            'OwnerID' => $param['Id']
        ];

        // 插入新用户数据到 account 表
        $accountResult = Db::name('account')->insert($accountData);
        if (!$accountResult) {
            return json(['status' => 'error', 'message' => 'Failed to create user']);
        }

        if ($existingClient) {
            // 更新客户信息的 CardNum 字段，增加新卡号
            $updatedCardNum = $existingClient['CardNum'] + 1;
            $clientResult = Db::name('client')->where('Id', $clientId)->update(['CardNum' => $updatedCardNum]);
        } else {
            // 创建新客户信息
            $clientData = [
                'Name' => $param['Name'],
                'Sex' => $param['Sex'],
                'Id' => $param['Id'],
                'Phone' => $param['Phone'],
                'Address' => $param['Address'],
                'CardNum' => 1
            ];

            // 插入客户数据到 client 表
            $clientResult = Db::name('client')->insert($clientData);
            if (!$clientResult) {
                return json(['status' => 'error', 'message' => 'Failed to create or update client']);
            }
        }

        return json(['status' => 'success', 'message' => 'User and client created successfully', 'CardId' => $cardID]);
    }

    private function generateUniqueCardID()
    {
        do {
            $cardID = $this->generateCardID();
        } while ($this->isCardIDExists($cardID));

        return $cardID;
    }

    private function generateCardID()
    {
        $randomDigits = $this->generateRandomDigits(9); // 随机生成9位数字
        $partialCardID = $this->prefix . $randomDigits;
        $checkDigit = $this->calculateLuhnCheckDigit($partialCardID);
        return $partialCardID . $checkDigit;
    }

    private function generateRandomDigits($length)
    {
        $digits = '0123456789';
        $randomDigits = '';
        for ($i = 0; $i < $length; $i++) {
            $randomDigits .= $digits[rand(0, 9)];
        }
        return $randomDigits;
    }

    private function calculateLuhnCheckDigit($number)
    {
        $sum = 0;
        $alt = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];

            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
            $alt = !$alt;
        }

        return ($sum % 10 == 0) ? 0 : (10 - $sum % 10);
    }

    private function isCardIDExists($cardID)
    {
        $exists = Db::name('account')->where('CardID', $cardID)->find();
        return !empty($exists);
    }

    // 检查密码是否为强密码
    private function isStrongPassword($password)
    {
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,20}$/';
        return preg_match($pattern, $password);
    }
}
