<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 4:32 PM
 */

namespace JoseChan\Examination\Admin\Extensions\Actions;


use Illuminate\Contracts\Support\Renderable;

class ExamRange implements Renderable
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
        $uri = url("/admin/examination/range/{$this->key}");

        return <<<EOT
<a href="{$uri}" title="考试排名">
    考试排名
</a>
EOT;
    }

    public function __toString()
    {
        return $this->render();
    }
}
