<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/** Rend une vue dans le gabarit commun de l'application. */
final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): void
    {
        if (preg_match('/\A[a-zA-Z0-9_\/-]+\z/', $template) !== 1) {
            throw new RuntimeException('Nom de vue invalide.');
        }

        $templatePath = dirname(__DIR__) . '/Views/' . $template . '.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException(sprintf('La vue "%s" est introuvable.', $template));
        }

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $templatePath;
            $content = ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        if ($content === false) {
            throw new RuntimeException('Impossible de générer le contenu de la vue.');
        }

        require dirname(__DIR__) . '/Views/layout.php';
    }
}
