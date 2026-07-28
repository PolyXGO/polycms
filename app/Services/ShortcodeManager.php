<?php

namespace App\Services;

class ShortcodeManager
{
    /**
     * @var array<string, callable>
     */
    protected array $shortcodes = [];

    public function add(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }

    public function remove(string $tag): void
    {
        unset($this->shortcodes[$tag]);
    }

    public function exists(string $tag): bool
    {
        return isset($this->shortcodes[$tag]);
    }

    public function clear(): void
    {
        $this->shortcodes = [];
    }

    public function all(): array
    {
        return $this->shortcodes;
    }

    public function do(string $content): string
    {
        if (empty($this->shortcodes) || !str_contains($content, '[')) {
            return $content;
        }

        $pattern = $this->getShortcodeRegex();

        return preg_replace_callback("/$pattern/s", function ($m) {
            return $this->doShortcodeTag($m);
        }, $content);
    }

    public function strip(string $content): string
    {
        if (empty($this->shortcodes)) {
            return $content;
        }

        $pattern = $this->getShortcodeRegex();

        return preg_replace("/$pattern/s", '$1$6', $content);
    }

    public function parseAtts(string $text): array
    {
        $atts = [];
        $text = trim($text);

        if (empty($text)) {
            return $atts;
        }

        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s"\']+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[1])) {
                    $atts[strtolower($match[1])] = stripcslashes($match[2]);
                } elseif (!empty($match[3])) {
                    $atts[strtolower($match[3])] = stripcslashes($match[4]);
                } elseif (!empty($match[5])) {
                    $atts[strtolower($match[5])] = stripcslashes($match[6]);
                } elseif (isset($match[7]) && strlen($match[7])) {
                    $atts[] = stripcslashes($match[7]);
                } elseif (isset($match[8]) && strlen($match[8])) {
                    $atts[] = stripcslashes($match[8]);
                } elseif (isset($match[9])) {
                    $atts[] = stripcslashes($match[9]);
                }
            }
        } else {
            $atts = ltrim($text);
        }

        return $atts;
    }

    public function mergeAtts(array $pairs, array $atts, string $shortcode = ''): array
    {
        $atts = (array) $atts;
        $out = [];

        foreach ($pairs as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }

        return $out;
    }

    protected function doShortcodeTag(array $m): string
    {
        // Escaped shortcode like [[tag]]
        if ($m[1] === '[' && $m[6] === ']') {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];

        if (!$this->exists($tag)) {
            return $m[0];
        }

        $atts = $this->parseAtts($m[3]);
        $callback = $this->shortcodes[$tag];
        $content = $m[5] ?? null;

        if (is_array($callback) && is_string($callback[0])) {
            $callback[0] = app($callback[0]);
        }

        return call_user_func($callback, $atts, $content ?? '', $tag);
    }

    protected function getShortcodeRegex(): string
    {
        $tagregexp = implode('|', array_map('preg_quote', array_keys($this->shortcodes)));

        return
            '\\['
            . '(\\[?)'
            . '(' . $tagregexp . ')'
            . '(?![\\w-])'
            . '('
            .     '[^\\]\\/]*'
            .     '(?:\\/(?!\\])[^\\]\\/]*?)*'
            . ')'
            . '(?:(\\/\\])'
            .     '|'
            .     '\\]'
            .         '(?:'
            .             '('
            .                 '[^\\[]*+'
            .                 '(?:'
            .                     '\\[(?!\\/\\2\\])'
            .                     '[^\\[]*+'
            .                 ')*+'
            .             ')'
            .             '\\[\\/\\2\\]'
            .         ')?'
            . ')'
            . '(\\]?)';
    }
}

