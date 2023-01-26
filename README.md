## 考试系统

#### 安装

````
composer require "jose-chan/examination-admin"
````

- 安装系统所需的数据库

````
php artisan examination:install
````

- 发布模版文件到public文件夹

````bash
php artisan vendor:publish --provider=JoseChan\Examination\Admin\Providers\ExaminationServiceProvider
````

