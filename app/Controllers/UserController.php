<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;

class UserController extends BaseController
{
    use ResponseTrait;
    protected $users;
    public function __construct()
    {
        $this->users = new UserModel();
    }
    public function index()
    {
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
        $user = $decoded->data->username;
        try {
            $data = $this->users->where('username !=', $user)->findColumn("username");
        } catch (\Throwable $tr) {
            return $this->fail("database get error");
        }

        return $this->setResponseFormat('json')->respondCreated($data);
    }
}
