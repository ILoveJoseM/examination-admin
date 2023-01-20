<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 10:25 PM
 */

namespace JoseChan\Examination\Admin\Extensions\ImportHandler;


use Carbon\Carbon;
use Encore\Admin\Actions\Response;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use JoseChan\Examination\DataSet\Models\Question;
use Maatwebsite\Excel\Facades\Excel;

class ExamineeImport extends AbstractHandler
{
    const HEADER_USERNAME = "用户名";
    const HEADER_PASSWORD = "密码";
    const HEADER_NAME = "名称";

    const HEADERS = [
        self::HEADER_USERNAME => "username",
        self::HEADER_PASSWORD => "password",
        self::HEADER_NAME => "name",
    ];

    const REQUIRE_FIELD = ["username", "password", "name"];


    function handle(Request $request, Response $response): Response
    {
        $file = $request->file('file');

        try {
            $content = $file->get();
        } catch (FileNotFoundException $e) {
            return $response->topCenter()->warning("文件上传失败");
        }

        if (!$content) {
            return $response->topCenter()->warning("打开文件失败");
        }
        $file = $file->move(storage_path(), md5($content) . "." . $file->extension());
        /** @var \Maatwebsite\Excel\Excel $excel */
        $array = Excel::toArray(new ImportToArray(), $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename());

        $sheet1 = $array[0] ?? [];

        $headers = $sheet1[0] ?? [];

        unset($sheet1[0]);
        $bodies = array_values($sheet1);
        $fields = [];
        foreach ($headers as $key => $header) {
            if (isset(self::HEADERS[$header])) {
                $fields[$key] = self::HEADERS[$header];
                continue;
            }

            return $response->topCenter()->warning("导入失败，模版格式不正确");
        }
        $time = new Carbon();


        $users = [];
        foreach ($bodies as $row) {
            $fillable = [
                "created_at" => $time,
                "updated_at" => $time,
            ];

            foreach ($fields as $key => $field) {
                if (in_array($field, self::REQUIRE_FIELD) && empty($row[$key])) {
                    $headerName = array_flip(self::HEADERS);
                    return $response->topCenter()->warning($headerName[$field] . "不能为空");
                }

                if ($field == self::HEADERS[self::HEADER_PASSWORD]) {
                    $row[$key] = Hash::make($row[$key]);
                }

                $fillable[$field] = $row[$key];
            }

            $users[] = $fillable;
        }

        // 获取角色
        $roleModel = config('admin.database.roles_model');
        /** @var Role|null $role */
        $role = $roleModel::query()->where('slug', "=", "examinee")->first();
        $roleId = $role ? $role->id : 0;

        $userModel = config('admin.database.users_model');

        // 获取用户
        $roleUserTable = config("admin.database.role_users_table");
        try {
            DB::beginTransaction();
            foreach ($users as $user) {
                $userId = $userModel::query()->insertGetId($user);

                if (!empty($roleId)) {
                    DB::table($roleUserTable)->insert(["role_id" => $roleId, "user_id" => $userId]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $response->topCenter()->warning("导入失败，写表错误");
        }


        return $response->topCenter()->success("导入成功")->refresh();
    }

    /**
     * @param $header
     * @return bool
     */
    private function isOptions($header)
    {
        return strpos($header, "选项") === 0;
    }

    /**
     * @param $header
     * @return bool|string
     */
    private function getOptions($header)
    {
        return strtoupper(mb_substr($header, 2, null, "UTF-8"));
    }

    /**
     * @param $str
     * @return bool
     */
    private function strAZ($str)
    {
        $no = ord($str);
        return $no >= 65 && $no <= 90;
    }
}
