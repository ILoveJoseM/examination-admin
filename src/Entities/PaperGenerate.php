<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/14/23
 * Time: 3:34 PM
 */

namespace JoseChan\Examination\Admin\Entities;


use JoseChan\Entity\Entity;
use JoseChan\Entity\Factory\IEntityFactory;
use JoseChan\Examination\Admin\FormException;
use JoseChan\Examination\DataSet\Models\ExaminationSubject;
use JoseChan\Examination\DataSet\Models\Paper;
use JoseChan\Examination\DataSet\Models\PaperQuestion;
use JoseChan\Examination\DataSet\Models\Question;
use JoseChan\Examination\DataSet\Models\QuestionBank;

/**
 * Class PaperGenerate
 * @package JoseChan\Examination\Admin\Entities
 * @property string $bank_id
 * @property string $type
 * @property string $single_score
 * @property string $single_num
 * @property string $multi_score
 * @property string $multi_num
 * @property string $judge_score
 * @property string $judge_num
 */
class PaperGenerate extends Entity
{

    /**
     * @param ExaminationSubject $examinationSubject
     * @throws FormException
     * @return ExaminationSubject
     */
    public function getPaperQuestions(ExaminationSubject $examinationSubject)
    {
        $paper = Paper::query()->newModelInstance([]);
        $counter = $this->countBankQuestion()->pluck("num", "type");
        if (!empty($this->single_num) && $counter->get(Question::TYPE_SINGLE) < $this->single_num) {
            throw (new FormException("题库单选题数量不足"))->setField("type1_num");
        }

        if (!empty($this->multi_num) && $counter->get(Question::TYPE_MULTI) < $this->multi_num) {
            throw (new FormException("题库多选题数量不足"))->setField("type2_num");
        }

        if (!empty($this->judge_num) && $counter->get(Question::TYPE_JUDGE) < $this->judge_num) {
            throw (new FormException("题库判断题数量不足"))->setField("type3_num");
        }


        $paperQuestions = collect();
        if (!empty($this->single_num) && $this->single_num > 0) {
            $paperQuestions = $paperQuestions->merge(
                $this->generate(Question::TYPE_SINGLE, $this->single_num, $this->single_score)
            );
        }

        if (!empty($this->multi_num) && $this->multi_num > 0) {
            $paperQuestions = $paperQuestions->merge(
                $this->generate(Question::TYPE_SINGLE, $this->multi_num, $this->multi_score)
            );
        }

        if (!empty($this->judge_num) && $this->judge_num > 0) {
            $paperQuestions = $paperQuestions->merge(
                $this->generate(Question::TYPE_SINGLE, $this->judge_num, $this->judge_score)
            );
        }

        $paper->setRelation("question", $paperQuestions);
        $examinationSubject->setRelation("paper", $paper);

        return $examinationSubject;
    }

    protected function countBankQuestion()
    {
        return Question::query()
            ->selectRaw("count(*) as num, type")
            ->where("question_bank_id", "=", $this->bank_id)
            ->groupBy("type")->get();
    }

    protected function generate($type, $num, $score)
    {
        $ids = $this->getQuestionIds($type);

        shuffle($ids);

        $ids = array_values($ids);

        $ids = array_slice($ids, 0, $num);

        /** @var Question[] $questions */
        $questions = $this->getQuestionByIds($ids);

        $paperQuestions = collect();
        foreach ($questions as $question) {
            $paperQuestions->push(PaperQuestion::query()->newModelInstance([
                "type" => $type,
                "content" => $question->content,
                "description" => $question->description,
                "options" => $question->options,
                "answer" => $question->answer,
                "score" => $score,
            ]));
        }

        return $paperQuestions;
    }

    /**
     * @param $ids
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Question[]
     */
    protected function getQuestionByIds($ids)
    {
        return Question::query()->whereIn("id", $ids)->get();
    }

    /**
     * @param $type
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getQuestionIds($type)
    {
        return Question::query()
            ->where("question_bank_id", "=", $this->bank_id)
            ->where("type", "=", $type)
            ->get(['id'])->pluck("id")->toArray();
    }
}
