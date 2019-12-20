<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WxGoodsModel extends Model
{
    //数据表名
    protected $table = 'p_wx_goods';
    //主键id
    protected $primaryKey = 'gid';
}
