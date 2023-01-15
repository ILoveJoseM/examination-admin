<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 4:54 PM
 */

namespace JoseChan\Examination\Admin\Extensions\Tools;


use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\Response;
use Illuminate\Http\Request;
use JoseChan\Examination\Admin\Extensions\ImportHandler\AbstractHandler;
use JoseChan\Examination\Admin\Extensions\ImportHandler\ImportType;

class ImportPost extends Action
{
    public $name = '导入数据';

    protected $selector = '.import-post';

    protected $type;

    protected $importType;

    protected $extends = [];

    public function __construct(ImportType $importType)
    {
        $this->importType = $importType;
        parent::__construct();
    }

    public function handle(Request $request)
    {
        $type = $request->get("type");
        $handler = $this->importType->getTypeHandler($type);
        if (!empty($handler) && $handler instanceof AbstractHandler) {
            return $handler->handle($request, $this->response());
        }

        return $this->response()->success("success");
    }

    public function form()
    {
        $this->file('file', '请选择文件')->rules("mimes:xlsx");
        $this->hidden("type")->default($this->type);
        foreach ($this->extends as $key => $extend){
            $this->hidden($key)->default($extend);
        }
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default import-post"><i class="fa fa-upload"></i>{$this->name}</a>
HTML;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $type
     * @return ImportPost
     */
    public function setType($type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param array $extends
     * @return $this
     */
    public function setExtends(array $extends)
    {
        $this->extends = $extends;
        return $this;
    }


}
