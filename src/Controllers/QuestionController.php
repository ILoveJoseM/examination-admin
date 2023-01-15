<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-08 16:38:16
 */

namespace JoseChan\Examination\Admin\Controllers;

use JoseChan\Examination\Admin\Extensions\ImportHandler\BankQuestionImport;
use JoseChan\Examination\Admin\Extensions\ImportHandler\ImportType;
use JoseChan\Examination\Admin\Extensions\Tools\DownloadImportTemplate;
use JoseChan\Examination\Admin\Extensions\Tools\ImportPost;
use JoseChan\Examination\DataSet\Models\Question;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class QuestionController extends Controller
{

    use HasResourceActions;

    public function index($bank_id)
    {
        return Admin::content(function (Content $content) use ($bank_id) {

            //页面描述
            $content->header('题目管理');
            //小标题
            $content->description('题目管理');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '题目管理', 'url' => "/question_bank/{$bank_id}/questions"]
            );

            $content->body($this->grid($bank_id));
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($bank_id, $id)
    {
        return Admin::content(function (Content $content) use ($bank_id, $id) {

            $content->header('题目管理');
            $content->description('编辑');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '题目管理', 'url' => '/question_bank/{bank_id}/questions'],
                ['text' => '编辑']
            );

            $content->body($this->form($bank_id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create($bank_id)
    {
        return Admin::content(function (Content $content) use ($bank_id) {

            $content->header('题目管理');
            $content->description('新增');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '题目管理', 'url' => '/question_bank/{bank_id}/questions'],
                ['text' => '新增']
            );

            $content->body($this->form($bank_id));
        });
    }

    public function grid($bank_id)
    {
        return Admin::grid(Question::class, function (Grid $grid) use ($bank_id) {

            $grid->model()->where("question_bank_id", $bank_id);
            $grid->column("id", "ID")->sortable();
//            $grid->column("question_bank_id", "题库ID");
            $grid->column("type", "题目类型")->using([1 => "单选题", 2 => "多选题", 3 => "判断题"])->sortable();
            $grid->column("content", "题目");
            $grid->column("options", "选项")->display(function ($options){
                $options = json_decode($options, true);
                $display = <<<HTML

HTML;

                foreach ($options as $option => $value){
                    $display .= <<<HTML
{$option}: {$value}<br/>
HTML;
                }

                return $display;

            });
            $grid->column("answer", "答案");
            $grid->column("created_at", "创建时间")->sortable();
            $grid->column("updated_at", "更新时间")->sortable();
            $grid->tools(function (Grid\Tools $tools) use ($bank_id){
                $tools->append(app(ImportPost::class)
                    ->setName("导入题目")
                    ->setType(ImportType::BANK_QUESTION)
                    ->setExtends([
                        "bank_id" => $bank_id
                    ])
                );
                $tools->append((new DownloadImportTemplate())->setFile("templates/question_template.xlsx"));
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableView();
                $actions->disableEdit();
            });


            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter) {

                $filter->equal("question_bank_id", "题库ID");
                $filter->equal("type", "题目类型")->select([1 => "单选题", 2 => "多选题", 3 => "判断题"]);


            });


        });
    }

    protected function form($bank_id)
    {
        return Admin::form(Question::class, function (Form $form) use ($bank_id) {

            $form->display('id', "ID");
            $form->hidden('question_bank_id', "题库ID")->default($bank_id);
            $form->select("type", "题目类型")->options([1 => "单选题", 2 => "多选题", 3 => "判断题"]);

            $form->textarea('content', '题目')->rules("required|string");
            $form->textarea('description', '解析')->rules("required|string");
            $form->textarea('options', '选项')->rules("required|string");
            $form->text('answer', "答案")->rules("required|string");
            $form->datetime('created_at', "创建时间");
            $form->datetime('updated_at', "更新时间");


        });
    }
}
