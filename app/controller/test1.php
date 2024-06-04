<?php

namespace app\controller;
use app\BaseController;
class test1
{
    public function respondWithJson()
    {
        // 设置响应头为 JSON
        header('Content-Type: application/json');

        // 创建要返回的数据
        $data = [
            'status' => 'success',
            'message' => 'This is a response from RuanjianTest1',
            'data' => [
                'id' => 1,
                'name' => 'Example Data'
            ]
        ];

        // 将数据转换为 JSON 并输出
        echo json_encode($data);
    }
}