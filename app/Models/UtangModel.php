<?php

namespace App\Models;

use CodeIgniter\Model;

class UtangModel extends Model
{
    protected $table      = 'daftar_ngutang';
    protected $primaryKey = 'ngutangID';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['username1', 'username2', 'diff_amount', 'updated_at'];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
