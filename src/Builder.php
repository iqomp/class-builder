<?php

/**
 * Build class file content based on structured array
 * @package iqomp/class-builder
 * @version 0.0.1
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

    protected static function genMethods(array $methods, array $uses = []): string
    {
        $tx = '';
        $nl = PHP_EOL;
        $s  = '    ';

        foreach ($methods as $name => $attr) {
            $tx .= $s;
            $tx .= self::implementPrefix($attr);
            $tx .= 'function ' . $name;
            $tx .= ' (';
            if (isset($attr['arguments'])) {
                $tx .= self::implementArguments($attr['arguments']);
            }
            $tx .= ')';
            $tx .= self::implementSuffix($attr);
            $tx .= $nl;
            $tx .= $s . '{' . $nl;
            $tx .= $s . '}' . $nl . $nl;
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
                $tx .= ' = ' . Source::toSource($attr['default']);
            }

            $attrs[] = $tx;
        }

        return implode(', ', $attrs);
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
        return [];
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

        $tx .= $nl;
        $tx .= $data['type'];
        $tx .= ' ';
        $tx .= $data['name'];

        if (isset($data['extends'])) {
            $tx .= self::genExtends($data['extends'], $data['uses']);
        }

        if (isset($data['implements'])) {
            $tx .= self::genImplements($data['implements'], $data['uses']);
        }

        $tx .= $nl;
        $tx .= '{';
        $tx .= $nl;

        if (isset($data['properties'])) {
            $tx .= self::genProperties($data['properties'], $data['uses']);
        }

        if (isset($data['methods'])) {
            $tx .= self::genMethods($data['methods'], $data['uses']);
        }

        $tx = chop($tx, $nl);
        $tx .= $nl;
        $tx .= '}';

        return $tx;
    }
}
