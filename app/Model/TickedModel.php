<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TickedModel extends Model
{
    //数据表名
    protected $table = 'p_wx_ticked';
    //主键id
    protected $primaryKey = 'tid';
}
