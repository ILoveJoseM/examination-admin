<?php

/**
 * Created by JoseChan/Admin/ControllerCreator.
 * User: admin
 * DateTime: 2023-01-14 18:02:54
 */

namespace JoseChan\Examination\Admin\Controllers;

use function foo\func;
use JoseChan\Examination\Admin\Extensions\ImportHandler\ImportType;
use JoseChan\Examination\Admin\Extensions\Tools\DownloadImportTemplate;
use JoseChan\Examination\Admin\Extensions\Tools\ImportPost;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class PaperQuestionController extends Controller
{

    use HasResourceActions;

    public function index($paper_id)
    {
        return Admin::content(function (Content $content) use ($paper_id) {

            //页面描述
            $content->header('试卷题目列表');
            //小标题
            $content->description('试卷题目列表');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '试卷题目列表', 'url' => "/admin/paper/{$paper_id}/questions"]
            );

            $content->body($this->grid($paper_id));
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($paper_id, $id)
    {
        return Admin::content(function (Content $content) use ($paper_id, $id) {

            $content->header('试卷题目列表');
            $content->description('编辑');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '试卷题目列表', 'url' => "/admin/paper/{$paper_id}/questions"],
                ['text' => '编辑']
            );

            $content->body($this->form($paper_id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create($paper_id)
    {
        return Admin::content(function (Content $content) use ($paper_id) {

            $content->header('试卷题目列表');
            $content->description('新增');

            //面包屑导航，需要获取上层所有分类，根分类固定
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/'],
                ['text' => '试卷题目列表', 'url' => "/admin/paper/{$paper_id}/questions"],
                ['text' => '新增']
            );

            $content->body($this->form($paper_id));
        });
    }

    public function grid($paper_id)
    {
        return Admin::grid(PaperQuestion::class, function (Grid $grid) use ($paper_id) {

            $grid->model()->where("paper_id", $paper_id);
            $grid->column("id","ID");
            $grid->column("type","题目类型")->using([1=>"单选题", 2=>"多选题",3=>"判断题"])->sortable();
            $grid->column("content","题目");
            $grid->column("answer","答案");
            $grid->column("score","分值");
            $grid->column("created_at","创建时间")->sortable();
            $grid->column("updated_at","更新时间")->sortable();

            $grid->disableCreateButton();
            $grid->actions(function (Grid\Displayers\Actions $actions){
               $actions->disableView();
               $actions->disableEdit();
            });
            $grid->tools(function (Grid\Tools $tools) use ($paper_id){
                $tools->append(app(ImportPost::class)
                    ->setName("导入题目")
                    ->setType(ImportType::PAGER_QUESTION)
                    ->setExtends([
                        "paper_id" => $paper_id
                    ])
                );
                $tools->append((new DownloadImportTemplate())->setFile("templates/page_question_template.xlsx"));
            });

            //允许筛选的项
            //筛选规则不允许用like，且搜索字段必须为索引字段
            //TODO: 使用模糊查询必须通过搜索引擎，此处请扩展搜索引擎
            $grid->filter(function (Grid\Filter $filter){
                $filter->equal("type","题目类型")->select([1=>"单选题", 2=>"多选题",3=>"判断题"]);
            });
        });
    }

    protected function form($paper_id)
    {
        return Admin::form(PaperQuestion::class, function (Form $form) use ($paper_id) {

            $form->display('id',"ID");
            $form->hidden('paper_id',"试卷ID")->default($paper_id);
            $form->select("type","题目类型")->options([1=>"单选题", 2=>"多选题",3=>"判断题"]);

            $form->textarea('content', '题目')->rules("required|string");
            $form->textarea('description', '解析')->rules("required|string");
            $form->textarea('options', '选项')->rules("required|string");
            $form->text('answer',"答案")->rules("required|string");
            $form->text('score',"分值")->rules("required|integer");

        });
    }
}
