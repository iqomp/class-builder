<?php

/**
 * Build class file content based on structured array
 * @package iqomp/class-builder
 * @version 1.3.1
 */

namespace Iqomp\ClassBuilder;

class Builder
{
    protected static function genComment(array $comments, int $space = 0): string
    {
        $nl = PHP_EOL;
        $s = str_repeat(' ', $space);

        $tx = $s . '/**' . $nl;

        foreach ($comments as $line) {
            $tx .= $s . ' * ' . $line . $nl;
        }

        $tx .= $s . ' */';

        return $tx;
    }

    protected static function genExtends(array $extends, array $uses = []): string
    {
        $tx = '';
        $nl = PHP_EOL;
        $text = implode(', ', $extends);

        if (count($extends) > 1) {
            $tx .= $nl;
            $tx .= '    extends ';
        } else {
            $tx .= ' extends ';
        }

        $uses = [];
        foreach ($extends as $extend) {
            $uses[] = $uses[$extend] ?? $extend;
        }

        $tx .= implode(', ', $uses);

        return $tx;
    }

    protected static function genImplements(array $implements, $uses = []): string
    {
        $tx = '';
        $nl = PHP_EOL;
        $text = implode(', ', $implements);

        if (count($implements) > 1) {
            $tx .= $nl;
            $tx .= '    implements ';
        } else {
            $tx .= ' implements ';
        }

        $uses = [];
        foreach ($implements as $implement) {
            $uses[] = $uses[$implement] ?? $implement;
        }

        $tx .= implode(', ', $uses);

        return $tx;
    }

    protected static function genMethods(array $methods, array $uses = [], bool $ifs = false): string
    {
        $tx = '';
        $nl = PHP_EOL;
        $s  = '    ';

        foreach ($methods as $name => $attr) {
            if (isset($attr['comment'])) {
                $tx .= self::genComment($attr['comment'], 4);
                $tx .= $nl;
            }
            $tx .= $s;
            $tx .= self::implementPrefix($attr);
            $tx .= 'function ' . $name;
            $tx .= '(';
            if (isset($attr['arguments'])) {
                $tx .= self::implementArguments($attr['arguments']);
            }
            $tx .= ')';
            $tx .= self::implementSuffix($attr);
            if ($ifs) {
                $tx .= ';';
            } else {
                $tx .= $nl;
                $tx .= $s . '{' . $nl;
                if (isset($attr['content'])) {
                    $tx .= self::implementMethodContent($attr['content']);
                }
                $tx .= $s . '}';
            }

            $tx .= $nl . $nl;
        }

        return $tx;
    }

    protected static function genProperties(array $props, array $uses = []): string
    {
        $tx = '';
        $nl = PHP_EOL;

        foreach ($props as $name => $attr) {
            $tx .= '    ';
            $tx .= self::implementPrefix($attr);
            $tx .= '$' . $name;

            if (array_key_exists('default', $attr)) {
                $tx .= ' = ';
                $tx .= Source::toSource($attr['default'], 4);
            }

            $tx .= ';' . $nl . $nl;
        }

        return $tx;
    }

    protected static function implementArguments(array $args): string
    {
        $attrs = [];
        foreach ($args as $name => $attr) {
            $tx = self::implementPrefix($attr);
            $tx .= '$' . $name;
            if (array_key_exists('default', $attr)) {
                $tx .= ' = ' . Source::toSource($attr['default'], 0, true);
            }

            $attrs[] = $tx;
        }

        return implode(', ', $attrs);
    }

    protected static function implementMethodContent(string $content): string
    {
        $nl = PHP_EOL;
        $lines = explode($nl, $content);

        $indent = 80;
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }

            $c_len = strlen($line);
            $t_len = strlen(ltrim($line));
            $s_len = $c_len - $t_len;

            if ($s_len < $indent) {
                $indent = $s_len;
            }
        }

        $tx = '';
        $s = str_repeat(' ', 8);
        foreach ($lines as $line) {
            $e_line = chop($line);
            if (!$e_line && !$tx) {
                continue;
            }

            $u_line = substr($line, $indent);
            $u_line = chop($u_line);

            $tx .= chop($s . $u_line) . $nl;
        }

        return chop($tx) . $nl;
    }

    protected static function implementPrefix(array $attr): string
    {
        $prefs = [];
        if (isset($attr['visibility'])) {
            $prefs[] = $attr['visibility'];
        }

        if (isset($attr['static']) && $attr['static']) {
            $prefs[] = 'static';
        }

        if (isset($attr['type'])) {
            $prefs[] = $attr['type'];
        }

        if (!$prefs) {
            return '';
        }

        return implode(' ', $prefs) . ' ';
    }

    protected static function implementSuffix(array $attr): string
    {
        if (!isset($attr['return'])) {
            return '';
        }

        return ': ' . $attr['return'];
    }

    protected static function parseUsesClasses(array $data): array
    {
        return $data['uses'] ?? [];
    }

    public static function build(array $data): string
    {
        $nl = PHP_EOL;

        $data['uses'] = self::parseUsesClasses($data);

        $tx = '<?php' . $nl;

        if (isset($data['comments'])) {
            $tx .= $nl;
            $tx .= self::genComment($data['comments'], 0);
            $tx .= $nl;
        }

        if (isset($data['namespace'])) {
            $tx .= $nl;
            $tx .= 'namespace ' . $data['namespace'] . ';';
            $tx .= $nl;
        }

        if (isset($data['uses'])) {
            $tx .= $nl;
            foreach ($data['uses'] as $class => $alt) {
                $tx .= 'use ' . $class;
                if ($alt) {
                    $tx .= ' as ' . $alt;
                }
                $tx .= ';';
                $tx .= $nl;
            }

            $tx = chop($tx, $nl);
        }

        if (isset($data['class_comments'])) {
            $tx .= $nl;
            $tx .= self::genComment($data['class_comments'], 0);
        }

        $tx .= $nl;
        $tx .= $data['type'];
        $tx .= ' ';
        $tx .= $data['name'];

        if (isset($data['extends']) && $data['extends']) {
            $tx .= self::genExtends($data['extends'], $data['uses']);
        }

        if (isset($data['implements']) && $data['implements']) {
            $tx .= self::genImplements($data['implements'], $data['uses']);
        }

        $tx .= $nl;
        $tx .= '{';
        $tx .= $nl;

        if (isset($data['properties'])) {
            $tx .= self::genProperties($data['properties'], $data['uses']);
        }

        if (isset($data['methods'])) {
            $is_ifs = $data['type'] == 'interface';
            $tx .= self::genMethods($data['methods'], $data['uses'], $is_ifs);
        }

        $tx = chop($tx, $nl);
        $tx .= $nl;
        $tx .= '}';

        return $tx;
    }
}
