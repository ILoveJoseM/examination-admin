<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-08 10:01:53
 */

namespace JoseChan\Examination\Admin\Controllers;

use Illuminate\Database\Eloquent\Collection;
use JoseChan\Examination\Admin\Extensions\Actions\ExamRange;
use JoseChan\Examination\Admin\Extensions\Actions\SubjectRange;
use JoseChan\Examination\DataSet\Models\Examination;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use JoseChan\Examination\DataSet\Models\ExaminationSubject;
use JoseChan\Examination\DataSet\Models\Subject;
use JoseChan\Examination\DataSet\Models\UserExaminationSubject;

class UserExaminationRangeController extends Controller
{

    use HasResourceActions;

    static $sort;

    public function index()
    {
        return Admin::content(function (Content $content) {

            //页面描述
            $content->header('考试成绩排名');
//            小标题
//            $content->description('考试设计');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试成绩排名', 'url' => '/examination']
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
        return Admin::grid(ExaminationSubject::class, function (Grid $grid) {

            $grid->column("id", "id")->sortable();
            $grid->column("examination.name", "考试名称");
            $grid->column("subject.name", "科目名称");
            $grid->disableCreateButton();
            $grid->disableExport();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append(new ExamRange($actions->getResource(), $actions->row->examination->id));
                $actions->append(new SubjectRange($actions->getResource(), $actions->row->id));
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

    public function exam($examId)
    {
        return Admin::content(function (Content $content) use ($examId) {

            /** @var Examination $exam */
            $exam = Examination::query()
                ->where("id", "=", $examId)
                ->first();
            /** @var ExaminationSubject|Collection $examSubject */
            $examSubject = ExaminationSubject::query()
                ->where("examination_id", "=", $examId)
                ->get(["id"]);
            $examSubjectIds = $examSubject->pluck("id")->toArray();
            //页面描述
            $content->header($exam->name . "考试成绩");
            //小标题
            $content->description('考试成绩排名');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试成绩排名', 'url' => '/examination']
            );

            $content->body(Admin::grid(UserExaminationSubject::class, function (Grid $grid) use ($examSubjectIds) {
                $grid->disablePagination();
                $grid->paginate(100);
                $grid->model()
                    ->whereIn("examination_subject_id", $examSubjectIds)
                    ->groupBy("uid")
                    ->orderByDesc("score")
                    ->selectRaw("uid, sum(score) as score");
                $grid->column("排名")->display(function () {
                    self::$sort ++;
                    return self::$sort;
                });
                $grid->column("adminUser.name", "考生");
                $grid->column("score", "得分");
                $grid->disableCreateButton();
                $grid->disableExport();
                $grid->disableActions();

                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableView();
                    $actions->disableEdit();
                    $actions->disableDelete();
                });

            }));
        });

    }

    public function subject($examSubjectId)
    {
        return Admin::content(function (Content $content) use ($examSubjectId) {

            /** @var ExaminationSubject $examSubject */
            $examSubject = ExaminationSubject::query()
                ->with(["examination", "subject"])
                ->where("id", "=", $examSubjectId)
                ->first();
            //页面描述
            $content->header($examSubject->examination->name . "-" . $examSubject->subject->name . "科 考试成绩");
            //小标题
            $content->description('考试成绩排名');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试成绩排名', 'url' => '/examination']
            );

            $content->body(Admin::grid(UserExaminationSubject::class, function (Grid $grid) use ($examSubjectId) {
                $grid->disablePagination();
                $grid->paginate(100);
                $grid->model()->where("examination_subject_id", $examSubjectId)->orderByDesc("score");
                $grid->column("排名")->display(function () {
                    self::$sort ++;
                    return self::$sort;
                });
                $grid->column("adminUser.name", "考生");
                $grid->column("score", "得分");
                $grid->disableCreateButton();
                $grid->disableExport();
                $grid->disableActions();

                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableView();
                    $actions->disableEdit();
                    $actions->disableDelete();
                });

            }));
        });
    }
}
