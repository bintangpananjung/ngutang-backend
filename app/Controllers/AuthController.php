<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;

class AuthController extends BaseController
{
    use ResponseTrait;
    protected $users;
    public function __construct()
    {
        $this->users = new UserModel();
    }
    public function login()
    {
        helper(['form']);
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $req = $this->request;
        try {
            $user = $this->users->where('username', $req->getVar('username'))->first();
        } catch (\Throwable $err) {
            return $this->fail($err);
        }

        if (!$user) {
            return $this->fail("user tidak ditemukan");
        }
        $verify = password_verify($req->getVar('password'), $user['password']);
        if (!$verify) {
            return $this->fail("password salah");
        }
        $key = getenv('SECRET_KEY_JWT');

        $iat = time(); // current timestamp value
        $nbf = $iat;
        $exp = $iat + 604800;

        $payload = array(
            "iat" => $iat, // issued at
            "nbf" => $nbf, //not before in seconds
            "exp" => $exp, // expire time in seconds
            "data" => [
                "username" => $req->getVar('username')
            ]
        );
        $token = JWT::encode($payload, $key);
        $data = [
            'token' => $token,
            'username' => $req->getVar('username')
        ];

        return $this->setResponseFormat('json')->respondCreated($data);
    }
    public function register()
    {
        helper(['form']);
        $rules = [
            'username' => 'required|is_unique[users.username]',
            'email' => 'required|is_unique[users.email]|valid_email',
            'password' => 'required'
        ];

        $req = $this->request;

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'username' => $req->getVar('username'),
            'email' => $req->getVar('email'),
            'password' => password_hash($req->getVar('password'), PASSWORD_BCRYPT)
        ];

        $this->users->save($data);

        return $this->setResponseFormat('json')->respondNoContent("registrasi berhasil");
    }
    public function profile()
    {
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
        $data = [
            "username" => $decoded->data->username
        ];
        return $this->setResponseFormat('json')->respondCreated($data);
    }
}
