<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private $data;
    private $rules;
    private $errors = [];
    private $customMessages = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
    }

    public static function make(array $data, array $rules, array $messages = [])
    {
        return new self($data, $rules, $messages);
    }

    public function validate()
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function fails()
    {
        return !$this->validate();
    }

    private function applyRule($field, $value, $rule)
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $method = 'validate' . ucfirst($rule);
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    private function addError($field, $rule, $params = [])
    {
        $message = $this->getMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }

    private function getMessage($field, $rule, $params = [])
    {
        $key = "{$field}.{$rule}";
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        $messages = [
            'required' => 'Поле обязательно для заполнения',
            'email' => 'Некорректный email',
            'min' => 'Минимальная длина: ' . ($params[0] ?? 0) . ' символов',
            'max' => 'Максимальная длина: ' . ($params[0] ?? 255) . ' символов',
            'numeric' => 'Должно быть числом',
            'integer' => 'Должно быть целым числом',
            'string' => 'Должно быть строкой',
            'boolean' => 'Должно быть true или false',
            'in' => 'Недопустимое значение',
            'confirmed' => 'Подтверждение не совпадает',
            'same' => 'Значения не совпадают',
        ];

        return $messages[$rule] ?? "Поле не прошло валидацию ({$rule})";
    }

    private function validateRequired($field, $value, $params)
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
        }
    }

    private function validateEmail($field, $value, $params)
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    private function validateMin($field, $value, $params)
    {
        $min = (int)($params[0] ?? 0);
        if ($value === null || $value === '') return;
        $length = is_string($value) ? mb_strlen($value) : (is_numeric($value) ? $value : 0);
        if ($length < $min) {
            $this->addError($field, 'min', [$min]);
        }
    }

    private function validateMax($field, $value, $params)
    {
        $max = (int)($params[0] ?? 255);
        if ($value === null || $value === '') return;
        $length = is_string($value) ? mb_strlen($value) : (is_numeric($value) ? $value : 0);
        if ($length > $max) {
            $this->addError($field, 'max', [$max]);
        }
    }

    private function validateNumeric($field, $value, $params)
    {
        if ($value === null || $value === '') return;
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    private function validateInteger($field, $value, $params)
    {
        if ($value === null || $value === '') return;
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'integer');
        }
    }

    private function validateString($field, $value, $params)
    {
        if ($value === null || $value === '') return;
        if (!is_string($value)) {
            $this->addError($field, 'string');
        }
    }

    private function validateBoolean($field, $value, $params)
    {
        if ($value === null || $value === '') return;
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        if (!in_array($value, $acceptable, true)) {
            $this->addError($field, 'boolean');
        }
    }

    private function validateIn($field, $value, $params)
    {
        if ($value === null || $value === '') return;
        if (!in_array($value, $params, true)) {
            $this->addError($field, 'in');
        }
    }

    private function validateConfirmed($field, $value, $params)
    {
        $confirmation = $this->data["{$field}_confirmation"] ?? null;
        if ($value !== $confirmation) {
            $this->addError($field, 'confirmed');
        }
    }

    private function validateSame($field, $value, $params)
    {
        $other = $this->data[$params[0] ?? null] ?? null;
        if ($value !== $other) {
            $this->addError($field, 'same');
        }
    }
}
