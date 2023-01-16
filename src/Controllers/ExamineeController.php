<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/16/23
 * Time: 8:53 PM
 */

namespace JoseChan\Examination\Admin\Controllers;


use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Controllers\UserController;
use Encore\Admin\Grid\Displayers\Actions;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Tools;
use Illuminate\Support\Facades\DB;
use JoseChan\Examination\Admin\Extensions\ImportHandler\ImportType;
use JoseChan\Examination\Admin\Extensions\Tools\DownloadImportTemplate;
use JoseChan\Examination\Admin\Extensions\Tools\ImportPost;

class ExamineeController extends UserController
{
    protected function grid()
    {
        // 获取角色
        $roleModel = config('admin.database.roles_model');
        /** @var Role|null $role */
        $role = $roleModel::query()->where('slug', "=", "examinee")->first();
        $roleId = $role ? $role->id : 0;
        // 获取用户
        $roleUserTable = config("admin.database.role_users_table");
        $userIds = DB::table($roleUserTable)->where("role_id", "=", $roleId)->get(["user_id"])->pluck("user_id");
        empty($userIds) && $userIds = [0];
        $grid = parent::grid(); // TODO: Change the autogenerated stub
        $grid->model()->whereIn("id", $userIds);
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function (Actions $actions){
           $actions->disableView();
        });

        $grid->tools(function (Tools $tools) {
            $tools->append(app(ImportPost::class)
                ->setName("导入考生")
                ->setType(ImportType::EXAMINEE)
            );
            $tools->append((new DownloadImportTemplate())->setFile("templates/examinee_template.xlsx"));
        });

        $grid->filter(function (Filter $filter) {

            $filter->where(function ($query) {
                $query->where('name', 'like', "{$this->input}%");
            }, '名称');

            $filter->equal('username', '用户名');

        });
        return $grid;
    }
}
