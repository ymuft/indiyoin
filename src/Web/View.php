<?php

declare(strict_types=1);

namespace Indiyoin\Web;

use RuntimeException;

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'app'): void
    {
        if (!preg_match('/\A[a-zA-Z0-9_\/-]+\z/', $template) || !preg_match('/\A[a-zA-Z0-9_-]+\z/', $layout)) {
            throw new RuntimeException('Invalid view name.');
        }

        $views = dirname(__DIR__, 2) . '/views';
        $templateFile = $views . '/' . $template . '.php';
        $layoutFile = $views . '/layouts/' . $layout . '.php';
        if (!is_file($templateFile) || !is_file($layoutFile)) {
            throw new RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $templateFile;
        $content = (string) ob_get_clean();
        require $layoutFile;
    }
}
