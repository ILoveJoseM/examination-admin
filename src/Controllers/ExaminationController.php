<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-08 10:01:53
 */

namespace JoseChan\Examination\Admin\Controllers;

use JoseChan\Examination\Admin\Extensions\Actions\ExamSubjectList;
use JoseChan\Examination\DataSet\Models\Examination;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ExaminationController extends Controller
{

    use HasResourceActions;

    public function index()
    {
        return Admin::content(function (Content $content) {

            //页面描述
            $content->header('考试管理');
            //小标题
            $content->description('考试设计');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试管理', 'url' => '/examination']
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

            $content->header('考试管理');
            $content->description('编辑');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试管理', 'url' => '/examination'],
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

            $content->header('考试管理');
            $content->description('新增');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试管理', 'url' => '/examination'],
                ['text' => '新增']
            );

            $content->body($this->form());
        });
    }

    public function grid()
    {
        return Admin::grid(Examination::class, function (Grid $grid) {

            $grid->column("id", "id")->sortable();
            $grid->column("start_time", "考试开始时间")->sortable();
            $grid->column("end_time", "考试结束时间")->sortable();
            $grid->column("name", "考试名称");
            $grid->column("exam_time", "考试时长")->display(function ($examTime) {

                return $examTime . "分钟" ;
            });
            $grid->column("created_at", "创建时间")->sortable();
            $grid->column("updated_at", "更新时间")->sortable();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->append(new ExamSubjectList($actions->getResource(), $actions->getKey()));
            });

            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter) {

                $filter->where(function ($query) {
                    $query->where('name', 'like', "{$this->input}%");
                }, '考试名称');


            });


        });
    }

    protected function form()
    {
        return Admin::form(Examination::class, function (Form $form) {
            $form->datetime('start_time', "考试开始时间");
            $form->datetime('end_time', "考试结束时间");
            $form->text('name', "考试名称")->rules("required|string");
            $form->text('exam_time', "考试时长（分）")->rules("required|integer");
            $form->saving(function (Form $form) {
                $examTime = $form->input("exam_time");
                $examTime = $examTime * 60;
                $form->input("exam_time", $examTime);
            });
        });
    }
}
