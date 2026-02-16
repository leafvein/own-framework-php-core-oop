<?php
declare(strict_types = 1);

namespace App\Core;

class Response
{
    public static function view(string $view, array $data = [])
    {
        extract($data);
        $content = self::render($view, $data);
        include __DIR__ . '/../../views/layout.php';
        exit;
    }

    protected static function render(string $view, array $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . '/../../views/' . $view . '.php';
        return ob_get_clean();
    }

    public static function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
