<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Http\Request;
class LoginController extends Controller
{
    public function addUser()
    {
        $pass = '13245json';
        $email = 'lisi@qq.com';
        //使用密码函数
        $password = password_hash($pass,PASSWORD_BCRYPT);
        $data = [
           'user_name' => 'zhangsan',
           'password' =>  $password,
            'email' => $email,
        ];

        $uid = UserModel::insertGetId($data);
        var_dump($uid);
    }

}
