<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2019-09-07
 * Time: 14:58
 */

namespace JoseChan\Examination\Admin\Providers;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use JoseChan\Examination\Admin\Commands\ExaminationInstaller;
use JoseChan\Examination\Admin\Extensions\Bootings\ImportBooting;
use JoseChan\Examination\Admin\Extensions\Form\Fields\Import;

/**
 * 应用服务提供者
 * Class AppServiceProvider
 * @package JoseChan\App\Api\Providers
 */
class ExaminationServiceProvider extends RouteServiceProvider
{
    /** 定义命名空间 **/
    protected $namespace = "JoseChan\Examination\Admin\Controllers";

    /**
     * @var array
     */
    protected $commands = [
        ExaminationInstaller::class
    ];

    /**
     * 初始化
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'exam');
        $this->publishes([__DIR__ . '/../../templates' => public_path("templates")], "examination-admin");
        $this->bootingImporter();
        $this->commands($this->commands);
        parent::boot();
    }

    /**
     * 路由配置
     */
    public function map()
    {
        Route::prefix('admin')
            ->middleware(['web', 'admin'])
            ->namespace($this->namespace)
            ->group(__DIR__ . "/../../routes/routes.php");
    }

    public function bootingImporter(){
        if (! ImportBooting::boot()) {
            return ;
        }


        Admin::booting(function (){
            Form::extend('import', Import::class);
        });
    }

}
