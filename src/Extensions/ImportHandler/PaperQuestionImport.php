<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 10:25 PM
 */

namespace JoseChan\Examination\Admin\Extensions\ImportHandler;


use Encore\Admin\Actions\Response;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use JoseChan\Examination\DataSet\Models\Question;
use Maatwebsite\Excel\Facades\Excel;

class PaperQuestionImport extends AbstractHandler
{
    const HEADER_TYPE = "题目类型";
    const HEADER_QUESTION = "问题";
    const HEADER_ANSWER = "答案";
    const HEADER_SCORE = "分值";
    const HEADER_DESC = "解析";

    const HEADERS = [
        self::HEADER_TYPE => "type",
        self::HEADER_QUESTION => "content",
        self::HEADER_ANSWER => "answer",
        self::HEADER_SCORE => "score",
        self::HEADER_DESC => "description",
    ];

    const REQUIRE_FIELD = ["type", "content", "answer", "score"];

    const TYPE_SINGLE = "单选题";
    const TYPE_MULTI = "多选题";
    const TYPE_JUDGE = "判断题";

    const TYPES = [
        self::TYPE_SINGLE => Question::TYPE_SINGLE,
        self::TYPE_MULTI => Question::TYPE_MULTI,
        self::TYPE_JUDGE => Question::TYPE_JUDGE,
    ];

    function handle(Request $request, Response $response): Response
    {
        $file = $request->file('file');

        $paper_id = $request->get("paper_id");
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
        $options = [];
        $fields = [];
        foreach ($headers as $key => $header) {
            if ($this->isOptions($header)) {
                $op = $this->getOptions($header);
                if ($this->strAZ($op)) {
                    $options[$key] = $op;
                    continue;
                }
            }

            if (isset(self::HEADERS[$header])) {
                $fields[$key] = self::HEADERS[$header];
                continue;
            }

            return $response->topCenter()->warning("导入失败，模版格式不正确");
        }

        $questions = [];
        foreach ($bodies as $row) {
            $fillable = [
                "paper_id" => $paper_id
            ];
            $option = [];

            foreach ($options as $key => $optionName) {
                if(!empty($row[$key])){
                    $option[$optionName] = $row[$key];
                }
            }

            if (empty($option)) {
                return $response->topCenter()->warning("导入失败，至少有一个可选项");
            }

            foreach ($fields as $key => $field) {
                if (in_array($field, self::REQUIRE_FIELD) && empty($row[$key])) {
                    continue 2;
                }

                if ($field == self::HEADERS[self::HEADER_TYPE]) {
                    $row[$key] = self::TYPES[$row[$key]];
                }

                if ($field == self::HEADERS[self::HEADER_ANSWER]) {
                    if (!$this->strAZ(strtoupper($row[$key]))) {
                        return $response->topCenter()->warning("答案只能是A-Z");
                    }
                    $row[$key] = strtoupper($row[$key]);

                }
                $fillable[$field] = $row[$key];
            }

            $fillable["options"] = json_encode($option);
            $questions[] = $fillable;
        }

        PaperQuestion::query()->insert($questions);
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
