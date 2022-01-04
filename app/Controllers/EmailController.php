<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SumUtangModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;

class EmailController extends BaseController
{
    use ResponseTrait;
    protected $users;
    protected $email;
    protected $sumUtang;
    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->email->setFrom('dummyemailbntg@gmail.com', 'Ngutang');
        $this->users = new UserModel();
        $this->sumUtang = db_connect()->table('sum_ngutang');
    }
    public function index()
    {
        helper('time');
        $since = getSinceTimeMessage(Time::parse("2022-01-01 06:53:27")->getTimestamp(), Time::now()->getTimestamp());
        // return $this->setResponseFormat('json')->respondCreated($since);

        $data = [
            "username1" => "bambang",
            "username2" => "bintnag",
            "diff_amount" => 10000,
            "since" => $since
        ];
        echo view('template/email_tagih', $data);
    }
    public function notifybill()
    {
        helper(['header', 'time', 'utang']);
        $username1 = getHeaderJWT($this->request->getServer("HTTP_AUTHORIZATION"))->data->username;
        $req = $this->request;
        $query1 = [
            "username1" => $username1,
            "username2" => $req->getVar('username2')
        ];
        $query2 = [
            "username1" => $req->getVar('username2'),
            "username2" => $username1
        ];
        $sumtransaction = $this->sumUtang->where($query1)->orWhere($query2)->get()->getRowArray();
        $diff_amount = getAbsDiffAmount($sumtransaction['diff_amount']);
        $since = getSinceTimeMessage(Time::parse($sumtransaction['updated_at'])->getTimestamp(), Time::now()->getTimestamp());
        // return $this->setResponseFormat('json')->respondCreated($sumtransaction);
        // $email->setCC('another@another-example.com');
        // $email->setBCC('them@their-example.com');
        $data = [
            "username1" => $username1,
            "username2" => $req->getVar('username2'),
            "diff_amount" => $diff_amount,
            "since" => $since
        ];
        $email_user2 = $this->users->where('username', $req->getVar('username2'))->findColumn("email");
        $this->email->setTo($email_user2);

        $this->email->setSubject('Tagihan Utang : Ngutang');
        $this->email->setMessage(view('template/email_tagih', $data));

        try {
            $this->email->send();
            return $this->setResponseFormat('json')->respondCreated("email telah dikirim");
        } catch (\Throwable $tr) {
            return $this->fail("email gagal dikirim");
        }
    }
    public function notifypaidoff()
    {
        helper(['header', 'time', 'utang']);
        $username1 = getHeaderJWT($this->request->getServer("HTTP_AUTHORIZATION"))->data->username;
        $req = $this->request;
        $query1 = [
            "username1" => $username1,
            "username2" => $req->getVar('username2')
        ];
        $query2 = [
            "username1" => $req->getVar('username2'),
            "username2" => $username1
        ];
        $sumtransaction = $this->sumUtang->where($query1)->orWhere($query2)->get()->getRowArray();
        $diff_amount = getAbsDiffAmount($sumtransaction['diff_amount']);
        $data = [
            "username1" => $username1,
            "username2" => $req->getVar('username2'),
            "diff_amount" => $diff_amount
        ];
        $email_user2 = $this->users->where('username', $req->getVar('username2'))->findColumn("email");
        $this->email->setTo($email_user2);

        $this->email->setSubject('Tagihan Utang : Ngutang');
        $this->email->setMessage(view('template/email_lunas', $data));

        try {
            $this->email->send();
            return $this->setResponseFormat('json')->respondCreated("email telah dikirim");
        } catch (\Throwable $tr) {
            return $this->fail("email gagal dikirim");
        }
    }
}
