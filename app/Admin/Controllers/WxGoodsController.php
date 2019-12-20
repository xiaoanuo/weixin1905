<?php

namespace App\Admin\Controllers;

use App\Model\WxGoodsModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WxGoodsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品展示';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WxGoodsModel);

        $grid->column('gid', __('Gid'));
        $grid->column('good_name', __('Good name'));
        $grid->column('price', __('Price'));
        $grid->column('img', __('Img'))->image();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WxGoodsModel::findOrFail($id));

        $show->field('gid', __('Gid'));
        $show->field('good_name', __('Good name'));
        $show->field('price', __('Price'));
        $show->field('img', __('Img'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WxGoodsModel);

        $form->text('good_name', __('Good name'));
        $form->number('price', __('Price'));
        $form->image('img', __('Img'));

        return $form;
    }
}
