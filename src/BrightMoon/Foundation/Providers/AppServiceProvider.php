<?php

namespace BrightMoon\Foundation\Providers;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Mô tả
     *
     * @return mixed
     */
    public function boot()
    {
        //
    }

    /**
     * Đăng ký các dịch vụ chạy ngầm trong hệ thống.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \BrightMoon\Database\Query\Processors\Processor::class,
            fn ($app) => $app->make(\BrightMoon\Database\Query\Processors\Processor::class)->getProcessor()
        );
    }
}
