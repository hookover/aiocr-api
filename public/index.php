<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

if (env('XHPROF_ENABLED')) {
    xhprof_enable(XHPROF_FLAGS_NO_BUILTINS + XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    register_shutdown_function(function () {
        $data = xhprof_disable();   //返回运行数据
        //xhprof_lib 在下载的包里存在这个目录,记得将目录包含到运行的php代码中
        include '/srv/webroot/xhprof/xhprof_lib/utils/xhprof_lib.php';
        include '/srv/webroot/xhprof/xhprof_lib/utils/xhprof_runs.php';
        $objXhprofRun = new XHProfRuns_Default();
        $objXhprofRun->save_run($data, "test"); //test 表示文件后缀
    });
}

$app->run();
