<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 4:32 PM
 */

namespace JoseChan\Examination\Admin\Extensions\Actions;


use Illuminate\Contracts\Support\Renderable;
use JoseChan\Examination\DataSet\Models\Paper;

class PaperQuestionList implements Renderable
{
    protected $resource;
    protected $key;

    public function __construct($resource, $key)
    {
        $this->resource = $resource;
        $this->key = $key;
    }

    public function render()
    {
        /** @var Paper $paper */
        $paper = Paper::query()->where("examination_subject_id", $this->key)->first();
        $uri = url("/admin/paper/{$paper->id}/questions");

        return <<<EOT
<a href="{$uri}" title="题目列表">
    题目列表
</a>
EOT;
    }

    public function __toString()
    {
        return $this->render();
    }
}
