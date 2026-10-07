<?php

class View
{
    public static function render(string $template, array $data = []): void
    {
        // Controllers supply a template path relative to app/views/.
        $root = realpath(__DIR__ . '/views');
        $file = $root === false ? false : realpath($root . '/' . $template);

        if ($file === false || !is_file($file)
            || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0) {
            throw new RuntimeException('MVC view not found inside app/views.');
        }

        // An isolated scope prevents view data from replacing the include path
        // or exposing the caller's controller/router local variables.
        (static function (string $viewFile, array $viewData): void {
            extract($viewData, EXTR_SKIP);
            require $viewFile;
        })($file, $data);
    }
}
