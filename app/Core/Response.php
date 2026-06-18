<?php

namespace App\Core;

class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $content = '';
    private $version = '1.1';

    public function status($code)
    {
        $this->statusCode = (int)$code;
        return $this;
    }

    public function header($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function content($content)
    {
        $this->content = (string)$content;
        return $this;
    }

    public function json($data, $status = 200)
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function redirect($url, $status = 302)
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;
        return $this;
    }

    public function view($template, $data = [], $status = 200)
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->content = View::render($template, $data);
        return $this;
    }

    public function setCookie($name, $value, $options = [])
    {
        $defaults = [
            'expires' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        $options = array_merge($defaults, $options);

        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, $value, $options);
        } else {
            setcookie($name, $value, $options['expires'], $options['path'], $options['domain'], $options['secure'], $options['httponly']);
        }
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function send()
    {
        if (headers_sent()) {
            return $this;
        }

        $statusText = $this->getStatusText();
        header(sprintf('HTTP/%s %d %s', $this->version, $this->statusCode, $statusText), true, $this->statusCode);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value), true);
        }

        echo $this->content;
        return $this;
    }

    private function getStatusText()
    {
        $statuses = [
            200 => 'OK', 201 => 'Created', 204 => 'No Content',
            301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified',
            400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 422 => 'Unprocessable Entity',
            500 => 'Internal Server Error', 503 => 'Service Unavailable'
        ];
        return $statuses[$this->statusCode] ?? 'OK';
    }
}
