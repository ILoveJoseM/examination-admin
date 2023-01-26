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
    protected $status;

    public function __construct($resource, $key, $status)
    {
        $this->resource = $resource;
        $this->key = $key;
        $this->status = $status;
    }

    public function render()
    {
        $uri = url("/admin/user_examination_history/{$this->key}");

        if($this->status == 1){
            return <<<EOT
<a href="{$uri}" title="答题记录">
    答题记录
</a>
EOT;
        }

        return <<<EOT

EOT;


    }

    public function __toString()
    {
        return $this->render();
    }
}
