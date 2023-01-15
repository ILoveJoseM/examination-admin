<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 10:20 PM
 */

namespace JoseChan\Examination\Admin\Extensions\ImportHandler;


use Illuminate\Http\Request;
use Encore\Admin\Actions\Response;

abstract class AbstractHandler
{

    abstract function handle(Request $request, Response $response) : Response;
}
