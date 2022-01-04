<?php

namespace App\Models;

use CodeIgniter\Model;

class SumUtangModel extends Model
{
    protected $table      = 'sum_ngutang';
    protected $primaryKey = 'sumID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['username1', 'username2', 'diff_amount', 'updated_at'];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
