<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryModel extends Model
{
    protected $table = 'deliveries';
    protected $primaryKey = 'id';
    // Include transfer_id and type so deliveries can be linked and classified
    protected $allowedFields = ['order_id','transfer_id','type','driver_name','vehicle','route','status','scheduled_at','current_location','created_by','created_at','updated_at'];
    protected $useTimestamps = false;
}
