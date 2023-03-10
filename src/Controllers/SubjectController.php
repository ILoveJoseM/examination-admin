<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-08 10:27:08
 */

namespace JoseChan\Examination\Admin\Controllers;

use JoseChan\Examination\DataSet\Models\Subject;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class SubjectController extends Controller
{

    use HasResourceActions;

    public function index()
    {
        return Admin::content(function (Content $content) {

            //页面描述
            $content->header('科目管理');
            //小标题
            $content->description('科目管理');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '科目管理', 'url' => '/subject']
            );

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('科目管理');
            $content->description('编辑');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '科目管理', 'url' => '/subject'],
                ['text' => '编辑']
            );

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('科目管理');
            $content->description('新增');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '科目管理', 'url' => '/subject'],
                ['text' => '新增']
            );

            $content->body($this->form());
        });
    }

    public function grid()
    {
        return Admin::grid(Subject::class, function (Grid $grid) {

            $grid->column("id","ID")->sortable();
            $grid->column("name","科目名称");
            $grid->column("created_at","创建时间")->sortable();
            $grid->column("updated_at","更新时间")->sortable();


            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableView();
            });
            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter){

                $filter->where(function ($query) {
                    $query->where('name', 'like', "{$this->input}%");
                }, '科目名称');


            });


        });
    }

    protected function form()
    {
        return Admin::form(Subject::class, function (Form $form) {

            $form->display('id',"ID");
            $form->text('name',"科目名称")->rules("required|string");
            $form->datetime('created_at',"创建时间");
            $form->datetime('updated_at',"更新时间");


        });
    }
}
