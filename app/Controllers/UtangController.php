<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SumUtangModel;
use App\Models\UtangModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use Config\Database;
use Firebase\JWT\JWT;


class UtangController extends BaseController
{
    use ResponseTrait;
    protected $listUtang;
    protected $sumUtang;
    protected $listUtangBuilder;
    protected $sumUtangBuilder;
    public function __construct()
    {
        helper('header');
        $this->listUtang = new UtangModel();
        $this->sumUtang = new SumUtangModel();
        $this->listUtangBuilder = db_connect()->table('daftar_ngutang');
        $this->sumUtangBuilder = db_connect()->table('sum_ngutang');
    }
    public function index($pages = 1)
    {
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
        $user = $decoded->data->username;
        try {
            $countUtang = $this->sumUtangBuilder->where('username1', $user)->orWhere('username2', $user)->countAllResults();
            $offset = ($pages - 1) * 5;
            $tempUtang = $this->sumUtangBuilder->where('username1', $user)->orWhere('username2', $user)->orderBy('updated_at', 'DESC')->limit(5, $offset)->get()->getResultArray();
            $dataUtang = $tempUtang;
            $tempindex = 0;
            foreach ($tempUtang as $temp) {
                if ($temp['username2'] == $user) {
                    $dataUtang[$tempindex]['username1'] = $user;
                    $dataUtang[$tempindex]['username2'] = $temp['username1'];
                    $dataUtang[$tempindex]['diff_amount'] = $temp['diff_amount'] * -1;
                }
                $tempindex += 1;
            }
            $res = [
                "maxPage" => floor($countUtang / 5) + 1,
                "data" => $dataUtang
            ];
            // $dataUtang1 = $this->sumUtang->where('username1', $user)->findAll();
            // $tempUtang2 = $this->sumUtang->where('username2', $user)->findAll();
            // $dataUtang2 = $tempUtang2;
            // foreach ($tempUtang2 as $data) {
            //     $dataUtang2[0]['username1'] = $user;
            //     $dataUtang2[0]['username2'] = $data['username1'];
            //     $dataUtang2[0]['diff_amount'] = $data['diff_amount'] * -1;
            // }
            // $res = array_merge($dataUtang1, $dataUtang2);
            // usort($res, function ($a, $b) {
            //     return $b['updated_at'] <=> $a['updated_at'];
            // });
        } catch (\Throwable $err) {
            $this->fail($err);
        }
        return $this->setResponseFormat('json')->respondCreated($res);
    }
    public function history($pages = 1)
    {
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
        $user = $decoded->data->username;
        try {
            $countUtang = $this->listUtangBuilder->where('username1', $user)->orWhere('username2', $user)->countAllResults();
            $offset = ($pages - 1) * 5;
            $tempUtang = $this->listUtangBuilder->where('username1', $user)->orWhere('username2', $user)->orderBy('updated_at', 'DESC')->limit(5, $offset)->get()->getResultArray();
            $dataUtang = $tempUtang;
            $tempindex = 0;
            foreach ($tempUtang as $temp) {
                if ($temp['username2'] == $user) {
                    $dataUtang[$tempindex]['username1'] = $user;
                    $dataUtang[$tempindex]['username2'] = $temp['username1'];
                    $dataUtang[$tempindex]['diff_amount'] = $temp['diff_amount'] * -1;
                }
                $tempindex += 1;
            }
            $res = [
                "maxPage" => floor($countUtang / 5) + 1,
                "data" => $dataUtang
            ];
            // $dataUtang1 = $this->listUtang->where('username1', $user)->findAll();
            // $tempUtang2 = $this->listUtang->where('username2', $user)->findAll();
            // $dataUtang2 = $tempUtang2;
            // foreach ($tempUtang2 as $data) {
            //     $dataUtang2[0]['username1'] = $user;
            //     $dataUtang2[0]['username2'] = $data['username1'];
            //     $dataUtang2[0]['diff_amount'] = $data['diff_amount'] * -1;
            // }
            // $res = array_merge($dataUtang1, $dataUtang2);
            // usort($res, function ($a, $b) {
            //     return $b['updated_at'] <=> $a['updated_at'];
            // });
        } catch (\Throwable $err) {
            return $this->fail($err);
        }
        return $this->setResponseFormat('json')->respondCreated($res);
    }
    public function add()
    {
        $db      = db_connect();
        $sumUtang = $db->table('sum_ngutang');
        $req = $this->request;
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
        $user = $decoded->data->username;
        $data = [
            "username1" => $user,
            "username2" => $req->getVar('username2'),
            "diff_amount" => $req->getVar('diff_amount'),
        ];

        try {
            $this->listUtang->save($data);
            $query1 = [
                "username1" => $data['username1'],
                "username2" => $data['username2']
            ];
            $query2 = [
                "username1" => $data['username2'],
                "username2" => $data['username1']
            ];
            $sum_exist = $sumUtang->where($query1)->orWhere($query2)->get()->getRowArray();
            $now = Time::now();
            $data["updated_at"] = $now->toDateTimeString();
            // $sum_exist = $sumUtang->get()->getResultArray();
            // return $this->setResponseFormat('json')->respondCreated($sum_exist);
            if ($sum_exist) {
                $data["diff_amount"] += $sum_exist["diff_amount"];
                $sumUtang->where('sumID', $sum_exist['sumID'])->update($data);
            } else {
                $data["created_at"] = $now->toDateTimeString();
                $sumUtang->insert($data);
            };
        } catch (\Throwable $err) {
            $this->fail($err->getMessage());
        }
        return $this->setResponseFormat('json')->respondCreated($data);
    }

    public function userTransaction($user2)
    {

        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $decoded = getHeaderJWT($header);
        $user = $decoded->data->username;
        $where = "(username1 = '$user' and username2='$user2') or (username1 = '$user2' and username2='$user')";
        try {
            $tempUtang = $this->sumUtangBuilder->where($where)->get()->getRowArray();
            if (isset($tempUtang)) {
                $dataUtang = $tempUtang;
                if ($tempUtang['username2'] == $user) {
                    $dataUtang['username1'] = $user;
                    $dataUtang['username2'] = $tempUtang['username1'];
                    $dataUtang['diff_amount'] = $tempUtang['diff_amount'] * -1;
                }
                $res = $dataUtang;
                return $this->setResponseFormat('json')->respondCreated($res);
            } else {
                return $this->fail('user tidak ditemukan');
            }
        } catch (\Throwable $err) {
            $this->fail($err);
        }
    }
    public function historyUser($user2)
    {
        $req = $this->request;
        $pages = 1;
        if ($req->getGet('pages')) {
            $pages = $req->getGet('pages');
        }
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        $decoded = getHeaderJWT($header);
        $user = $decoded->data->username;
        $where = "(username1 = '$user' and username2='$user2') or (username1 = '$user2' and username2='$user')";
        try {
            $countUtang = $this->listUtangBuilder->where($where)->countAllResults();
            $offset = ($pages - 1) * 5;
            $tempUtang = $this->listUtangBuilder->where($where)->orderBy('updated_at', 'DESC')->limit(5, $offset)->get()->getResultArray();
            if (count($tempUtang)) {
                $dataUtang = $tempUtang;
                $tempindex = 0;
                foreach ($tempUtang as $temp) {
                    if ($temp['username2'] == $user) {
                        $dataUtang[$tempindex]['username1'] = $user;
                        $dataUtang[$tempindex]['username2'] = $temp['username1'];
                        $dataUtang[$tempindex]['diff_amount'] = $temp['diff_amount'] * -1;
                    }
                    $tempindex += 1;
                }
                $res = [
                    "maxPage" => floor($countUtang / 5) + 1,
                    "data" => $dataUtang
                ];
                return $this->setResponseFormat('json')->respondCreated($res);
            } else {
                $this->fail("user tidak ditemukan");
            }
        } catch (\Throwable $err) {
            return $this->fail($err);
        }
    }
}
