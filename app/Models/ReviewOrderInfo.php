<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReviewOrderInfo extends Model
{
    use HasFactory;
    public $table = 'reviewOrderInfo';
    public $timestamps = false;

     //删除审核信息
    public static function delReviewInfo($request) {
        return ReviewOrderInfo::select('bill')->where('userId','=',$request->userId)->where('tableNum','=',$request->tableNum)->delete(); 
    }
}


