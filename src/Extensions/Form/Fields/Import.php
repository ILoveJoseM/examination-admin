<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/14/23
 * Time: 10:09 AM
 */

namespace JoseChan\Examination\Admin\Extensions\Form\Fields;


use Encore\Admin\Form\Field;

class Import extends Field\File
{
    protected $view = "exam::file";

    protected $variables = [
        "url" => "#"
    ];

    public function setUrl($url)
    {
        $this->variables['url'] = $url;
        return $this;
    }
}
