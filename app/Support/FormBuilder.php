<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\URL;

class FormBuilder
{
    public function open(array $options = []): HtmlString
    {
        $method = strtoupper($options['method'] ?? 'POST');
        $formMethod = $method === 'GET' ? 'GET' : 'POST';

        $action = $this->getAction($options);

        $baseAttrs = ['method' => $formMethod, 'action' => $action];
        $skip = ['method', 'route', 'url', 'action', 'files'];
        $extra = array_diff_key($options, array_flip($skip));

        if (!empty($options['files'])) {
            $extra['enctype'] = 'multipart/form-data';
        }

        $html = '<form' . $this->buildAttributes(array_merge($baseAttrs, $extra)) . '>';

        if ($formMethod === 'POST') {
            $html .= csrf_field();
            if (!in_array($method, ['GET', 'POST'])) {
                $html .= method_field($method);
            }
        }

        return new HtmlString($html);
    }

    public function close(): HtmlString
    {
        return new HtmlString('</form>');
    }

    public function label(string $name, ?string $value = null, array $attributes = []): HtmlString
    {
        $attrs = $this->buildAttributes(array_merge(['for' => $name], $attributes));
        return new HtmlString('<label' . $attrs . '>' . e($value ?? $name) . '</label>');
    }

    public function text(string $name, $value = null, array $attributes = []): HtmlString
    {
        return $this->input('text', $name, $value, $attributes);
    }

    public function email(string $name, $value = null, array $attributes = []): HtmlString
    {
        return $this->input('email', $name, $value, $attributes);
    }

    public function password(string $name, array $attributes = []): HtmlString
    {
        return $this->input('password', $name, null, $attributes);
    }

    public function hidden(string $name, $value = null, array $attributes = []): HtmlString
    {
        return $this->input('hidden', $name, $value, $attributes);
    }

    public function number(string $name, $value = null, array $attributes = []): HtmlString
    {
        return $this->input('number', $name, $value, $attributes);
    }

    public function file(string $name, array $attributes = []): HtmlString
    {
        $attrs = $this->buildAttributes(array_merge(['type' => 'file', 'name' => $name], $attributes));
        return new HtmlString('<input' . $attrs . '>');
    }

    public function submit(string $value, array $attributes = []): HtmlString
    {
        $attrs = $this->buildAttributes(array_merge(['type' => 'submit', 'value' => $value], $attributes));
        return new HtmlString('<input' . $attrs . '>');
    }

    public function checkbox(string $name, $value = 1, $checked = false, array $attributes = []): HtmlString
    {
        $attrs = ['type' => 'checkbox', 'name' => $name, 'value' => $value];
        if ($checked) {
            $attrs['checked'] = 'checked';
        }
        $allAttrs = $this->buildAttributes(array_merge($attrs, $attributes));
        return new HtmlString('<input' . $allAttrs . '>');
    }

    public function select(string $name, array $options = [], $selected = null, array $attributes = []): HtmlString
    {
        $placeholder = $attributes['placeholder'] ?? null;
        unset($attributes['placeholder']);

        $attrs = $this->buildAttributes(array_merge(['name' => $name], $attributes));

        $html = '<select' . $attrs . '>';

        if ($placeholder !== null) {
            $html .= '<option value="">' . e($placeholder) . '</option>';
        }

        foreach ($options as $key => $display) {
            $isSelected = ((string) $key === (string) $selected) ? ' selected' : '';
            $html .= '<option value="' . e($key) . '"' . $isSelected . '>' . e($display) . '</option>';
        }

        $html .= '</select>';

        return new HtmlString($html);
    }

    protected function input(string $type, string $name, $value = null, array $attributes = []): HtmlString
    {
        $base = ['type' => $type, 'name' => $name];
        if ($value !== null) {
            $base['value'] = $value;
        }
        $attrs = $this->buildAttributes(array_merge($base, $attributes));
        return new HtmlString('<input' . $attrs . '>');
    }

    protected function getAction(array $options): string
    {
        if (isset($options['route'])) {
            $route = $options['route'];
            if (is_array($route)) {
                $name = array_shift($route);
                return route($name, $route);
            }
            return route($route);
        }

        if (isset($options['url'])) {
            return $options['url'];
        }

        if (isset($options['action'])) {
            return action($options['action']);
        }

        return URL::current();
    }

    protected function buildAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            if ($value === false) {
                continue;
            }
            if ($value === true) {
                $html .= ' ' . $key;
                continue;
            }
            $html .= ' ' . $key . '="' . e($value) . '"';
        }
        return $html;
    }
}
