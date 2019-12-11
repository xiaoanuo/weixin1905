<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WxUserModel extends Model
{
    //数据表名
    protected $table = 'p_wx_user';
    //主键id
    protected $primaryKey = 'uid';
}
