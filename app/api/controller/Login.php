<?php

namespace app\api\controller;

use app\admin\model\User;
use app\BaseController;
use think\Exception;

class Login extends BaseController
{
    public function index()
    {
        $post = $this->request->post();
        $rule = [
            'mobile|手机号'    =>  'require|mobile',
            'password|密码'    =>  'require',
        ];
        $this->apiValidate($post, $rule);

        try {
            $user = User::where(['mobile' => $post['mobile']])->find();

            if (empty($user)) {
                throw new Exception('用户不存在');
            }
            if (password($post['password']) != $user->password) {
                throw new Exception('密码不正确');
            }
            if ($user->status) {
                throw new Exception('账户已被冻结');
            }

            $hash = md5($user->id.$user->password);
            $token = signToken($user->id, $hash);

            $user->token = $token;
            $user->save();
        } catch (\Exception $e) {
            return result(0, '登录出错：'.$e->getMessage());
        }

        return result(1, '登录成功', ['token' => $token]);
    }

    public function register()
    {
        $post = $this->request->post();
        $rule = [
            'mobile|手机号'    =>  'require|mobile|unique:user',
            'password|密码'    =>  'require|min:6',
        ];
        $this->apiValidate($post, $rule);

        try {
            $post['password'] = password($post['password']);
            User::create($post);
        } catch (\Exception $e) {
            return result(0, '注册失败：'.$e->getMessage());
        }

        return result(1, '注册成功');
    }

}