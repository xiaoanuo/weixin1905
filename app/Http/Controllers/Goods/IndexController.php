<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Model\WxGoodsModel;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**商品详情页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Request $request)
    {
        $goods_id = $request->input('gid');
        $goods = WxGoodsModel::find($goods_id);
//        print_r($goods);
        $data = [
            'goods' => $goods
        ];
        return view('goods.detail',$data);
    }
}
