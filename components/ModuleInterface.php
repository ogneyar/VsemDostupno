<?php

namespace app\components;

interface ModuleInterface
{
    public function init();
    public function bootstrap($app);
}