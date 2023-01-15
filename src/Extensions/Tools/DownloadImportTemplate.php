<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 5:33 PM
 */

namespace JoseChan\Examination\Admin\Extensions\Tools;


use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Storage;

class DownloadImportTemplate extends AbstractTool
{
    protected $file;
    public function render()
    {
        $url = $this->file ? Storage::disk("public")->url($this->file) : "#";

        return <<<HTML
        <a class="btn btn-sm btn-default import-post" href="{$url}" target="_blank"><i class="fa fa-download"></i>下载导入模版</a>
HTML;
    }

    /**
     * @param mixed $file
     * @return self
     */
    public function setFile($file): self
    {
        $this->file = $file;
        return $this;
    }



}
