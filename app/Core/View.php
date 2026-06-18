<?php

namespace App\Core;

class View
{
    private static $shared = [];
    private static $sections = [];
    private static $currentSection = null;

    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            self::$shared = array_merge(self::$shared, $key);
        } else {
            self::$shared[$key] = $value;
        }
    }

    public static function render($template, $data = [])
    {
        $data = array_merge(self::$shared, $data);
        return self::renderFile($template, $data);
    }

    public static function exists($template)
    {
        return file_exists(self::resolvePath($template));
    }

    public static function startSection($name)
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection()
    {
        if (self::$currentSection === null) {
            return;
        }
        $content = ob_get_clean();
        self::$sections[self::$currentSection] = $content;
        self::$currentSection = null;
    }

    public static function yieldSection($name, $default = '')
    {
        return self::$sections[$name] ?? $default;
    }

    public static function escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function csrfField()
    {
        $token = self::$shared['csrf'] ?? '';
        return '<input type="hidden" name="_csrf" value="' . self::escape($token) . '">';
    }

    public static function method($method)
    {
        return '<input type="hidden" name="_method" value="' . self::escape($method) . '">';
    }

    public static function old($key, $default = '')
    {
        $old = $_SESSION['_old'][$key] ?? null;
        if ($old !== null) {
            return $old;
        }
        return $default;
    }

    public static function error($key)
    {
        $errors = $_SESSION['_errors'][$key] ?? [];
        if (empty($errors)) {
            return '';
        }
        $html = '<div class="invalid-feedback d-block">';
        foreach ($errors as $error) {
            $html .= self::escape($error) . '<br>';
        }
        $html .= '</div>';
        return $html;
    }

    public static function hasError($key)
    {
        return !empty($_SESSION['_errors'][$key]);
    }

    private static function renderFile($template, $data)
    {
        $path = self::resolvePath($template);
        if (!file_exists($path)) {
            throw new \Exception("View [{$template}] not found at {$path}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    private static function resolvePath($template)
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        return __DIR__ . '/../Views/' . $template . '.php';
    }
}
