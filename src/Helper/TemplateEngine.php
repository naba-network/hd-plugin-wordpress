<?php

namespace NabaHdwp\Helper;

defined('ABSPATH') || exit;

class TemplateEngine
{
    /**
     * Echo the rendered template directly.
     */
    public static function render(string $template_path, array $context = []): void
    {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo self::renderTemplate($template_path, $context);
    }

    /**
     * Render a template file with extracted context variables.
     * Templates are standard PHP files.
     */
    public static function renderTemplate(string $template_path, array $context = []): string
    {
        $realTemplatePath = NABA_HDWP_PLUGIN_PATH . $template_path;

        if (!file_exists($realTemplatePath)) {
            return "<!-- Template not found: {$realTemplatePath} -->";
        }

        // Extract context variables to make them accessible in the template
        extract($context, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include the standard PHP template
        include $realTemplatePath;

        // Capture and return output
        return ob_get_clean();
    }
}
