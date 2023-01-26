<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/25/23
 * Time: 5:43 PM
 */

namespace JoseChan\Examination\Admin\Commands;


use Carbon\Carbon;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ExaminationInstaller extends Command
{
    protected $name = "考试系统安装";

    protected $signature = "examination:install";

    public function handle()
    {
        $output = $this->getOutput();

        $output->writeln("开始安装考试系统");
        $output->writeln("正在创建数据表……");
        Artisan::call("migrate", [
            "--path" => "vendor/jose-chan/examination-dataset/database/migrations"
        ]);

        // 菜单
        $menuModel = config('admin.database.menu_model');
        /** @var Builder $menuBuilder */
        $menuBuilder = $menuModel::query();

        // 权限
        $permissionModel = config('admin.database.permissions_model');
        /** @var Builder $permissionBuilder */
        $permissionBuilder = $permissionModel::query();

        // 角色
        $roleModel = config('admin.database.roles_model');
        /** @var Builder $roleBuilder */
        $roleBuilder = $roleModel::query();


        try {
            DB::beginTransaction();// 写入菜单
            $output->writeln("正在创建考试设计菜单……");
            $designMenu = $this->saveExamDesignMenu($menuBuilder);
            $output->writeln("正在创建考试管理菜单……");
            $mangerMenu = $this->saveExamManagerMenu($menuBuilder);
            $output->writeln("正在创建考生权限……");
            $role = $this->saveRoles($roleBuilder);
            $output->writeln("正在为考生设置访问权限……");
            $managerPermission = $this->savePermissions($permissionBuilder);
            $this->saveRoleMenu($role, $mangerMenu);
            $this->saveRolePermission($role, $managerPermission, $permissionBuilder);
            DB::commit();
        } catch (\Exception $e) {
            $output->writeln("安装失败！错误原因：{$e->getMessage()}");
            DB::rollBack();
        }

        $output->writeln("安装完成！");

    }

    /**
     * @param Role $role
     * @param Menu $managerMenu
     */
    protected function saveRoleMenu(Role $role, Menu $managerMenu)
    {
        // 角色-菜单表
        $roleMenuTable = config('admin.database.role_menu_table');

        DB::table($roleMenuTable)->updateOrInsert([
            "role_id" => $role->id,
            "menu_id" => $managerMenu->id
        ], [
            "role_id" => $role->id,
            "menu_id" => $managerMenu->id
        ]);
    }

    protected function saveRolePermission(Role $role, Permission $mangerPermission, Builder $permissionBuilder)
    {
        // 获取登录、首页、用户设置权限的id
        $permissionIds = $permissionBuilder->whereIn("slug", [
            "dashboard",
            "auth.login",
            "auth.setting",
            "examinations"
        ])->get(["id"])->pluck("id")->toArray();

        $permissionIds[] = $mangerPermission->id;
        // 角色-权限表
        $rolePermissionTable = config('admin.database.role_permissions_table');

        foreach ($permissionIds as $id) {
            DB::table($rolePermissionTable)->updateOrInsert([
                "role_id" => $role->id,
                "permission_id" => $id
            ], [
                "role_id" => $role->id,
                "permission_id" => $id
            ]);
        }
    }

    /**
     * @param Builder $roleBuilder
     * @return \Illuminate\Database\Eloquent\Model|Role
     */
    protected function saveRoles(Builder $roleBuilder)
    {
        $role = $roleBuilder->newModelInstance([
            "name" => "考生",
            "slug" => "examinee"
        ]);
        $role->save();

        return $role;
    }

    /**
     * @param Builder $permissionBuilder
     * @return \Illuminate\Database\Eloquent\Model|Permission
     */
    protected function savePermissions(Builder $permissionBuilder)
    {
        $designPermission = $permissionBuilder->newModelInstance([
            "name" => "考试设计",
            "slug" => "exam_design",
            "http_path" => "/exminations\n/subject\n/question_banks\n/question_bank/*/questions\n/examination/*/subjects\n/api/subject_bank\n/paper/*/questions"
        ]);

        $managerPermission = $permissionBuilder->newModelInstance([
            "name" => "考试管理",
            "slug" => "examinations",
            "http_path" => "/user/examinations*\n/user/examination_subject*\n/user_examination_histories\n/user_examination_history*\n/user_examination_range\n/examination/range*\n/subject/range*"
        ]);

        $designPermission->save();
        $managerPermission->save();

        return $managerPermission;
    }

    /**
     * @param Builder $menuBuilder
     * @return Menu
     */
    protected function saveExamDesignMenu(Builder $menuBuilder)
    {
        // 考试设计
        /** @var Menu $examDesignParent */
        $examDesignParent = $menuBuilder->newModelInstance([
            "parent_id" => 0,
            "title" => "考试设计",
            "icon" => "fa-graduation-cap",
            "updated_at" => Carbon::now()
        ]);

        $examDesigns = $this->buildExamDesign($menuBuilder);

        $examDesignParent->save();
        $examDesignParent->children()->saveMany($examDesigns);
        return $examDesignParent;
    }

    /**
     * @param Builder $menuBuilder
     * @return array
     */
    protected function buildExamDesign(Builder $menuBuilder): array
    {
        $examDesigns = [
            [
                "title" => "考试管理",
                "icon" => "fa-clipboard",
                "updated_at" => Carbon::now(),
                "uri" => "/examinations"
            ],
            [
                "title" => "科目管理",
                "icon" => "fa-subscript",
                "updated_at" => Carbon::now(),
                "uri" => "/subject"
            ],
            [
                "title" => "题库管理",
                "icon" => "fa-book",
                "updated_at" => Carbon::now(),
                "uri" => "/question_banks"
            ],
            [
                "title" => "考生管理",
                "icon" => "fa-group",
                "updated_at" => Carbon::now(),
                "uri" => "/examinees"
            ],
        ];

        $examDesignModels = [];
        foreach ($examDesigns as $design) {
            $examDesignModels[] = $menuBuilder->newModelInstance($design);
        }
        return $examDesignModels;
    }

    /**
     * @param Builder $menuBuilder
     * @return Menu
     */
    protected function saveExamManagerMenu(Builder $menuBuilder)
    {
        // 考试设计
        /** @var Menu $examManagerParent */
        $examManagerParent = $menuBuilder->newModelInstance([
            "parent_id" => 0,
            "title" => "考试管理",
            "icon" => "fa-folder-open-o",
            "updated_at" => Carbon::now(),
        ]);

        $examManagers = $this->buildExamManager($menuBuilder);

        $examManagerParent->save();
        $examManagerParent->children()->saveMany($examManagers);
        return $examManagerParent;
    }

    /**
     * @param Builder $menuBuilder
     * @return array
     */
    protected function buildExamManager(Builder $menuBuilder): array
    {
        $examManagers = [
            [
                "title" => "开始考试",
                "icon" => "fa-hourglass-start",
                "updated_at" => Carbon::now(),
                "uri" => "/user/examinations"
            ],
            [
                "title" => "我的考试记录",
                "icon" => "fa-history",
                "updated_at" => Carbon::now(),
                "uri" => "/user_examination_histories"
            ],
            [
                "title" => "考试排名",
                "icon" => "fa-align-left",
                "updated_at" => Carbon::now(),
                "uri" => "/user_examination_range"
            ]
        ];

        $examManagerModels = [];
        foreach ($examManagers as $manager) {
            $examManagerModels[] = $menuBuilder->newModelInstance($manager);
        }
        return $examManagerModels;
    }
}
