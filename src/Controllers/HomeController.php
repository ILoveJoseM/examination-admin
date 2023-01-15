<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/15/23
 * Time: 9:25 PM
 */

namespace JoseChan\Examination\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('首页');
            $content->description('欢迎使用');

            $content->row(view('admin.title'));

            $content->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(view("exam::enter", [
                        "fa" => "fa-book",
                        "title" => "我的成绩",
                        "url" => "/admin/user_examination_histories",
                    ]));
                });

                $row->column(4, function (Column $column) {
                    $column->append(view("exam::enter", [
                        "fa" => "fa-hourglass-start",
                        "title" => "开始考试",
                        "url" => "/admin/user/examinations",
                    ]));
                });

                $row->column(4, function (Column $column) {
                    $column->append(view("exam::enter", [
                        "fa" => "fa-align-left",
                        "title" => "考试排名",
                        "url" => "/admin/user_examination_range",
                    ]));
                });
            });
        });
    }
}
