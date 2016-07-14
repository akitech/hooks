<?php

namespace hooks\MVC\TemplateEngine;

use hooks\MVC\TemplateEngine\Directives\ImageDirective;
use hooks\MVC\TemplateEngine\Directives\StringDirective;
use Razr\Loader\LoaderInterface;

class Engine extends \Razr\Engine
{
    public function __construct(LoaderInterface $loader, $cachePath)
    {
        parent::__construct($loader, $cachePath);
        $this->addDirective(new StringDirective);
        $this->addDirective(new ImageDirective);

    }

}