<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-15 08:25:50
 */

namespace JoseChan\Examination\Admin\Controllers;

use Encore\Admin\Actions\Action;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use function foo\func;
use JoseChan\Examination\Admin\Extensions\Actions\ExamResult;
use JoseChan\Examination\DataSet\Models\Examination;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use JoseChan\Examination\DataSet\Models\Question;
use JoseChan\Examination\DataSet\Models\Subject;
use JoseChan\Examination\DataSet\Models\UserExaminationSubject;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class UserExaminationSubjectRecordController extends Controller
{

    use HasResourceActions;

    public function index()
    {
        return Admin::content(function (Content $content) {

            //页面描述
            $content->header('考试记录');
            //小标题
            $content->description('我的考试记录');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试记录', 'url' => '/user_examination_subjects']
            );

            $content->body($this->grid());
        });
    }


    public function grid()
    {
        return Admin::grid(UserExaminationSubject::class, function (Grid $grid) {

            $uid = Admin::user()->id;
            $grid->model()
                ->with(["examinationSubject.examination", "examinationSubject.subject"])
                ->where("uid", "=", $uid)
                ->orderByDesc("id");
            $grid->column("id", "ID")->sortable();
            $grid->column("examinationSubject.examination", "考试")->display(function ($examination) {
                return $examination['name'];
            });
            $grid->column("examinationSubject.subject", "科目")->display(function ($subject) {
                return $subject['name'];
            });
            $grid->column("score", "分数")->sortable();
            $grid->column("status", "状态")->using([0 => '考试中', 1 => '已交卷']);
            $grid->column("created_at", "创建时间")->sortable();
            $grid->column("updated_at", "更新时间")->sortable();

            $grid->actions(function(Grid\Displayers\Actions $action){
                $action->disableDelete();
                $action->disableView();
                $action->disableEdit();
                $action->append(new ExamResult($action->getResource(), $action->getKey()));
            });

            $grid->disableExport();
            $grid->disableCreateButton();

            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter) {

                $filter->equal("examination_subject_id", "考试-科目id");
                $filter->equal("status", "状态")->select([0 => '考试中', 1 => '已交卷']);

            });


        });
    }

    protected function form($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试记录', 'url' => '/user_examination_histories'],
                ['text' => '错题集', 'url' => '/user_examination_history/' . $id]
            );

            /** @var UserExaminationSubject $userExaminationSubject */
            $userExaminationSubject = UserExaminationSubject::query()
                ->with("examinationSubject.paper.question")
                ->where("id", "=", $id)
                ->first();
            // 读取题目
            $questions = $userExaminationSubject->examinationSubject->paper->question->sortBy("type")->values();
            $questions = $questions->groupBy(function (PaperQuestion $question) {
                return $question->type;
            });

            $examinationSubject = $userExaminationSubject->examinationSubject;
            $content->header($userExaminationSubject->examinationSubject->subject->name);
            $content->description($userExaminationSubject->examinationSubject->examination->name . "错题集");

            $answers = $userExaminationSubject->answers;
            foreach ($questions as $questionType => $questionList) {
                $content->row(function (Row $row) use ($examinationSubject, $questionType, $questionList, $answers) {
                    $row->column(12, function (Column $column) use ($examinationSubject, $questionType, $questionList, $answers) {
                        $title = Question::TYPE_NAME[$questionType] ?? "";
                        $sort = Question::TYPE_SORT_NAME[$questionType] ?? "";
                        $column->append(view('exam::exam-result', compact('questionList', 'title', 'sort', 'questionType', 'answers')));
                    });
                });
            }
        });
    }
}
