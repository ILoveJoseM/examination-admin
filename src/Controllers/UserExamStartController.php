<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/15/23
 * Time: 10:31 AM
 */

namespace JoseChan\Examination\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JoseChan\Examination\DataSet\Models\ExaminationSubject;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use JoseChan\Examination\DataSet\Models\Question;
use JoseChan\Examination\DataSet\Models\UserExaminationSubject;

class UserExamStartController extends Controller
{
    public function index($examination_subject_id)
    {
        // 创建考生的考试
        $uid = Admin::user()->id;

        $userExaminationSubject = UserExaminationSubject::query()->where("uid", "=", $uid)
            ->where("status", "=", 0)
            ->first();

//        /** @var ExaminationSubject $examinationSubject */
//        $examinationSubject = ExaminationSubject::query()->where("id", $examination_subject_id)->first();

        if (empty($userExaminationSubject)) {
            UserExaminationSubject::query()->newModelInstance([
                "uid" => $uid,
                "answers" => "",
                "examination_subject_id" => $examination_subject_id,
            ])->save();
        }

        return Admin::content(function (Content $content) use ($examination_subject_id) {

            /** @var ExaminationSubject|null $examinationSubject */
            $examinationSubject = ExaminationSubject::query()
                ->where("id", "=", $examination_subject_id)
                ->with(["examination", "subject", "paper", "paper.question"])
                ->first();

            // 读取题目
            $questions = $examinationSubject->paper->question->sortBy("type")->values();
            $questions = $questions->groupBy(function (PaperQuestion $question) {
                return $question->type;
            });

            $content->header($examinationSubject->subject->name);
            $content->description($examinationSubject->examination->name);

            $content->body("<form>");
            $content->row(view('exam::exam-navbar-bar', [
                "exam_time" => $examinationSubject->examination->getOriginal("exam_time"),
                "examination_subject_id" => $examinationSubject->id
            ]));

            foreach ($questions as $questionType => $questionList) {
                $content->row(function (Row $row) use ($examinationSubject, $questionType, $questionList) {
                    $row->column(12, function (Column $column) use ($examinationSubject, $questionType, $questionList) {
                        $title = Question::TYPE_NAME[$questionType] ?? "";
                        $sort = Question::TYPE_SORT_NAME[$questionType] ?? "";
                        $column->append(view('exam::exam', compact('questionList', 'title', 'sort', 'questionType')));
                    });
                });
            }
        });
    }

    public function commit($examination_subject_id, Request $request)
    {

        try {
            $answer = $request->toArray();
            if (isset($answer['_token'])) {
                unset($answer['_token']);
            }
            $score = $this->computeScore($examination_subject_id, $answer);// 创建考生的考试
            $uid = Admin::user()->id;
            $userExaminationSubject = UserExaminationSubject::query()->where("uid", "=", $uid)
                ->where("status", "=", 0)
                ->first();
            $userExaminationSubject->update([
                "score" => $score,
                "status" => 1,
                "answers" => json_encode($answer, 320)
            ]);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "code" => 1]);
        }

        return response()->json(["message" => "success", "code" => 0, "id" => $userExaminationSubject->id]);
    }

    protected function computeScore($examination_subject_id, $answers)
    {
        $score = 0;
        // 获取试卷
        /** @var ExaminationSubject|null $examinationSubject */
        $examinationSubject = ExaminationSubject::query()
            ->where("id", "=", $examination_subject_id)
            ->with(["paper.question"])
            ->first();

        // 题目
        $questions = $examinationSubject->paper->question->keyBy("id");

        foreach ($answers as $questionId => $answer) {
            /** @var PaperQuestion $question */
            $question = $questions->get($questionId);

            $answer = implode("", $answer);
            if (strtoupper($answer) == strtoupper($question->answer)) {
                $score += (int)$question->score;
            }
        }

        return $score;

    }

    public function success($user_examination_subject_id)
    {
        return Admin::content(function (Content $content) use ($user_examination_subject_id) {

            $record = UserExaminationSubject::query()
                ->where("id", "=", $user_examination_subject_id)
                ->first();
            $score = $record->score;

            $content->row(view("exam::success", compact("score")));
        });
    }
}
