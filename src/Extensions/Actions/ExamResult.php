<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 4:32 PM
 */

namespace JoseChan\Examination\Admin\Extensions\Actions;


use Illuminate\Contracts\Support\Renderable;

class ExamResult implements Renderable
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
        $uri = url("/admin/user_examination_history/{$this->key}");

        return <<<EOT
<a href="{$uri}" title="答题记录">
    答题记录
</a>
EOT;
    }

    public function __toString()
    {
        return $this->render();
    }
}
