<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-14 09:12:02
 */

namespace JoseChan\Examination\Admin\Controllers;

use Encore\Admin\Actions\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use JoseChan\Examination\Admin\Entities\PaperGenerate;
use JoseChan\Examination\Admin\Extensions\Actions\PaperQuestionList;
use JoseChan\Examination\Admin\Extensions\Form\Fields\Import;
use JoseChan\Examination\Admin\Extensions\ImportHandler\PaperQuestionImport;
use JoseChan\Examination\Admin\FormException;
use JoseChan\Examination\DataSet\Models\ExaminationSubject;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use JoseChan\Examination\DataSet\Models\Paper;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use JoseChan\Examination\DataSet\Models\QuestionBank;
use JoseChan\Examination\DataSet\Models\Subject;

class ExaminationSubjectController extends Controller
{

    use HasResourceActions;

    public function index($exam_id)
    {
        return Admin::content(function (Content $content) use ($exam_id) {

            //页面描述
            $content->header('考试科目');
            //小标题
            $content->description('考试科目');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试科目', 'url' => "/examination/{$exam_id}/subjects"]
            );

            $content->body($this->grid($exam_id));
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($exam_id, $id)
    {
        return Admin::content(function (Content $content) use ($exam_id, $id) {

            $content->header('考试科目');
            $content->description('编辑');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试科目', 'url' => "/examination/{$exam_id}/subjects"],
                ['text' => '编辑']
            );

            $content->body($this->updateForm($exam_id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create($exam_id)
    {
        return Admin::content(function (Content $content) use ($exam_id) {

            $content->header('考试科目');
            $content->description('新增');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '考试科目', 'url' => "/examination/{$exam_id}/subjects"],
                ['text' => '新增']
            );

            $content->body($this->createForm($exam_id));
        });
    }

    public function grid($exam_id)
    {
        return Admin::grid(ExaminationSubject::class, function (Grid $grid) use ($exam_id) {

            $grid->model()->where("examination_id", "=", $exam_id);
            $grid->column("id", "ID");
            $grid->column("type", "考卷类型")->using([1 => "从题库随机", 2 => "导入试卷"]);
            $grid->column("subject_id", "科目")->using(Subject::getOptions());

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableView();
                $actions->append(new PaperQuestionList($actions->getResource(), $actions->getKey()));
            });

            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter) {

                $filter->equal("type", "考卷类型")->select([1 => "从题库随机", 2 => "导入试卷"]);

                $filter->equal("examination_id", "考试ID");
                $filter->equal("subject_id", "科目ID");


            });


        });
    }

    protected function createForm($exam_id)
    {
        return Admin::form(ExaminationSubject::class, function (Form $form) use ($exam_id) {

            $form->display('id', "ID");
            $form->hidden('examination_id', "考试ID")->default($exam_id);
            $form->select('subject_id', "科目")
                ->options(Subject::getOptions())
                ->load('bank_id', '/admin/api/subject_bank');
            $form->radioButton("type", "考卷类型")
                ->options([
                    1 => "从题库随机",
                    2 => "导入试卷"
                ])
                ->when(1, function (Form $form) {
                    $form->select('bank_id', "题库")->options([0 => "请先选择科目"]);
                    $form->checkbox("question_type", "题目类型")
                        ->options([1 => "单选题", 2 => "多选题", 3 => "判断题"])
                        ->when('has', 1, function (Form $form) {
                            $form->text("type1_score", "单选题分值");
                            $form->text("type1_num", "单选题数量");
                        })
                        ->when('has', 2, function (Form $form) {
                            $form->text("type2_score", "多选题分值");
                            $form->text("type2_num", "多选题数量");
                        })
                        ->when('has', 3, function (Form $form) {
                            $form->text("type3_score", "判断题分值");
                            $form->text("type3_num", "判断题数量");
                        });
                })
                ->when(2, function (Form $form) {
                    /** @var Import $importer */
                    $importer = $form->import("file", "导入试卷题目");
                    $url = Storage::disk("public")->url("templates/page_question_template.xlsx");
                    $importer->setUrl($url);
                });


        });
    }

    public function updateForm($exam_id)
    {
        return Admin::form(ExaminationSubject::class, function (Form $form) use ($exam_id) {

            $form->display('id', "ID");
            $form->hidden('examination_id', "考试ID")->default($exam_id);
            $form->select('subject_id', "科目")->options(Subject::getOptions());
        });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function bankOptions(Request $request)
    {
        $q = $request->get("q");

        return QuestionBank::getSubjectBankOptions($q);
    }

    public function store(Request $request)
    {
        $examId = $request->get("examination_id");
        $subjectId = $request->get("subject_id");
        $type = $request->get("type");
        $bankId = $request->get('bank_id');
        $questionTypes = $request->get('question_type');
        $questionTypes = is_array($questionTypes) ? array_filter($questionTypes) : [];

        if (empty($examId)) {
            return $this->error(["subject_id" => ["参数不正确"]], $request->except("question_type"));
        }

        if (empty($subjectId)) {
            return $this->error(["subject_id" => ["请选择考试科目"]], $request->except("question_type"));
        }

        if (empty($type)) {
            return $this->error(["type" => ["请选择试卷类型"]], $request->except("question_type"));
        }

        /** @var ExaminationSubject $examinationSubject */
        $examinationSubject = ExaminationSubject::query()->newModelInstance([
            "type" => $type,
            "examination_id" => $examId,
            "subject_id" => $subjectId
        ]);

        if($type == 1){
            if ($type == 1 && empty($bankId)) {
                return $this->error(["bank_id" => ["请选择题库"]], $request->except("question_type"));
            }

            if ($type == 1 && empty($questionTypes)) {
                return $this->error(["question_type" => ["至少有一种考试题目类型"]], $request->except("question_type"));
            }

            foreach ($questionTypes as $questionType) {
                $scoreField = "type{$questionType}_score";
                $numField = "type{$questionType}_num";


                if (empty($request->get($scoreField))) {
                    return $this->error([$scoreField => ["请填写题目分值"]], $request->except("question_type"));
                }

                if (empty($request->get($numField))) {
                    return $this->error([$numField => ["请填写题目数量"]], $request->except("question_type"));
                }

            }

            $data = [
                "bank_id" => $bankId,
                "type" => $type,
                "single_score" => $request->get("type1_score", 0),
                "single_num" => $request->get("type1_num", 0),
                "multi_score" => $request->get("type2_num", 0),
                "multi_num" => $request->get("type2_num", 0),
                "judge_score" => $request->get("type3_num", 0),
                "judge_num" => $request->get("type3_num", 0),
            ];

            /** @var PaperGenerate $paperGenerate */
            $paperGenerate = app(PaperGenerate::class, ["data" => $data]);

            try {
                $examinationSubject = $paperGenerate->getPaperQuestions($examinationSubject);
            } catch (FormException $e) {
                return $this->error([$e->getField() => [$e->getMessage()]], $request->except("question_type"));
            }
        }

        try {
            DB::beginTransaction();
            $examinationSubject->save();

            if($type == 1){
                /** @var Paper $paper */
                $paper = $examinationSubject->getRelation('paper');
                $paper->setAttribute($examinationSubject->paper()->getForeignKeyName(), $examinationSubject->paper()->getParentKey());
                $paper->save();
                /** @var Collection $questions */
                $questions = $paper->getRelation("question");
                $questions = $questions->map(function (PaperQuestion $question) use ($paper) {
                    $question->setAttribute($paper->question()->getForeignKeyName(), $paper->question()->getParentKey());
                    return $question;
                });
                PaperQuestion::query()->insert($questions->toArray());
            } else {
                /** @var Paper $paper */
                $paper = Paper::query()->newModelInstance([]);
                $paper->setAttribute($examinationSubject->paper()->getForeignKeyName(), $examinationSubject->paper()->getParentKey());
                $paper->save();
                $paperId = $paper->getKey();
                $request->offsetSet("paper_id", $paperId);
                $importer = new PaperQuestionImport();
                $importer->handle($request, (new Response())->toastr());
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(["subject_id" => ["保存失败：{$e->getMessage()}"]], $request->except("question_type"));
        }
        $form = $this->createForm($examId);
        $resourcesPath = $form->resource(0);
        return $this->redirectAfterSaving($resourcesPath);
    }

    public function update($exam_id, $id)
    {
        return $this->updateForm($exam_id)->update($id);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($messages, $input = [])
    {
        $messageBag = new MessageBag($messages);
        if (\request()->ajax() && !\request()->pjax()) {
            return response()->json([
                'status' => false,
                'validation' => $messageBag,
                'message' => $messageBag->first(),
            ]);
        }

        return back()->withInput($input)->withErrors($messageBag);
    }

    protected function redirectAfterSaving($resourcesPath, $key = 0)
    {
        admin_toastr(trans('admin.save_succeeded'));

        return redirect($resourcesPath);
    }
}
