<?php

namespace App\Models;

use CodeIgniter\Model;

class ComplaintItemModel extends Model
{
    protected $table = 'complaint_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['complaint_report_id','complaint_type','participant_id','contingent_id','participant_snapshot','contingent_snapshot','description'];
}
