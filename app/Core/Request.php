<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private $method;
    private $uri;
    private $path;
    private $query;
    private $post;
    private $get;
    private $files;
    private $server;
    private $cookies;
    private $headers;
    private $json;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH) ?? '/';
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->headers = $this->parseHeaders();
        $this->query = [];
        parse_str(parse_url($this->uri, PHP_URL_QUERY) ?? '', $this->query);

        // Для PUT/DELETE/PATCH — парсим php://input
        if (in_array($this->method, ['PUT', 'PATCH', 'DELETE']) && empty($this->post)) {
            $contentType = $this->getContentType();
            if (strpos($contentType, 'application/json') !== false) {
                $this->json = json_decode(file_get_contents('php://input'), true) ?? [];
                $this->post = $this->json;
            } else {
                parse_str(file_get_contents('php://input'), $this->post);
                $this->post = $this->post ?: [];
            }
        }

        if (empty($this->json)) {
            $this->json = [];
        }
    }

    public function method()
    {
        return $this->method;
    }

    public function path()
    {
        return $this->path;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function input($key, $default = null)
    {
        // Всегда проверяем JSON для PUT/PATCH/DELETE
        if (in_array($this->method, ['PUT', 'PATCH', 'DELETE']) && empty($this->post)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded[$key])) {
                return $decoded[$key];
            }
        }
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        if (isset($this->get[$key])) {
            return $this->get[$key];
        }
        if (isset($this->json[$key])) {
            return $this->json[$key];
        }
        return $default;
    }

    public function all()
    {
        return array_merge($this->get, $this->post, $this->json);
    }

    public function get($key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function json($key = null, $default = null)
    {
        if ($key === null) {
            return $this->json;
        }
        return $this->json[$key] ?? $default;
    }

    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    public function header($name, $default = null)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$name] ?? $default;
    }

    public function cookie($name, $default = null)
    {
        return $this->cookies[$name] ?? $default;
    }

    public function ip()
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }

    public function userAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    public function isAjax()
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function isJson()
    {
        return strpos($this->getContentType(), 'application/json') !== false;
    }

    public function isMethod($method)
    {
        return strtoupper($method) === $this->method;
    }

    public function isGet()
    {
        return $this->method === 'GET';
    }

    public function isPost()
    {
        return $this->method === 'POST';
    }

    public function isPut()
    {
        return $this->method === 'PUT';
    }

    public function isDelete()
    {
        return $this->method === 'DELETE';
    }

    public function has($key)
    {
        return isset($this->post[$key]) || isset($this->get[$key]) || isset($this->json[$key]);
    }

    public function only(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    public function bearerToken()
    {
        $auth = $this->header('Authorization', '');
        if (preg_match('/Bearer\s+(.+)$/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getContentType()
    {
        // Для PUT/PATCH/DELETE Content-Type может быть в $_SERVER['CONTENT_TYPE'] без HTTP_ префикса
        $fromHeader = $this->header('Content-Type', '');
        if (!empty($fromHeader)) {
            return $fromHeader;
        }
        return $this->server['CONTENT_TYPE'] ?? '';
    }

    private function parseHeaders()
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
