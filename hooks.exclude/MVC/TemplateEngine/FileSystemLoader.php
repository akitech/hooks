<?php

namespace hooks\MVC\TemplateEngine;


use Razr\Exception\RuntimeException;

class FileSystemLoader extends \Razr\Loader\FilesystemLoader
{
    protected $paths = [
        BASE_DIR . "/Views/_shared/",
        BASE_DIR . "/Views/",
        BASE_DIR . "/",
        ""
    ];

    public function __construct(array $paths = [])
    {
        $paths = array_merge( $paths, $this->paths );
        $paths = array_unique($paths);
        parent::__construct($paths);
    }

    protected function findTemplate($name)
    {
        $name = (string) $name;

        if(!endsWith($name, VIEWS_EXTENSION)){
            $name .= VIEWS_EXTENSION;
        }

        if (self::isAbsolutePath($name) && is_file($name)) {
            return $name;
        }

        $name = ltrim(strtr($name, '\\', '/'), '/');

        foreach ($this->paths as $path) {
            if (is_file($file = $path.'/'.$name)) {
                return $file;
            }
        }

        throw new RuntimeException(sprintf('Unable to find template "%s" (looked into: %s).', $name, implode(', ', $this->paths)));
    }

}