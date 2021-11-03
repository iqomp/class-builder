<?php

/**
 * PHP variable to text builder
 * @package iqomp/class-builder
 * @version 1.1.2
 */

namespace Iqomp\ClassBuilder;

class Source
{
    public static function fromArray(array $data, int $space = 0, bool $inline = false): string
    {
        if (!$data) {
            return '[]';
        }

        if (array_keys($data) !== range(0, count($data) - 1)) {
            return self::fromAssocArray($data, $space, $inline);
        } else {
            return self::fromArrayIndexed($data, $space, $inline);
        }

        return $tx;
    }

    public static function fromArrayIndexed(array $data, int $space = 0, bool $f_inline = false): string
    {
        $inline = true;

        if (!$f_inline) {
            $inable = ['boolean', 'integer', 'double', 'string'];
            foreach ($data as $index => $value) {
                $type = gettype($value);

                if (!in_array($type, $inable)) {
                    $inline = false;
                    break;
                }
            }
        }

        $nl = PHP_EOL;
        $s = str_repeat(' ', $space);
        $sn = str_repeat(' ', $space + 4);

        $tx = '[';
        if (!$inline) {
            $tx .= $nl;
        }

        foreach ($data as $index => $value) {
            if ($index) {
                $tx .= ',';
                if (!$inline) {
                    $tx .= $nl;
                }
            }

            if (!$inline) {
                $tx .= $sn;
            }

            $tx .= self::toSource($value, $space + 4);
        }

        if (!$inline) {
            $tx .= $nl;
            $tx .= $s;
        }

        $tx .= ']';

        return $tx;
    }

    public static function fromAssocArray(array $data, int $space = 0, bool $inline = false): string
    {
        $nl = PHP_EOL;
        $s = str_repeat(' ', $space);
        $sn = $s . '    ';

        $tx = '[';

        foreach ($data as $key => $value) {
            if (!$inline) {
                $tx .= $nl;
                $tx .= $sn;
            }
            $tx .= self::toSource($key);
            $tx .= ' => ';
            $tx .= self::toSource($value, $space + 4, $inline);
            $tx .= ',';
        }

        $tx = chop($tx, ',');

        if (!$inline) {
            $tx .= $nl . $s;
        }
        $tx .= ']';

        return $tx;
    }

    public static function toSource($vars, int $space = 0, bool $inline = false): string
    {
        if (is_string($vars)) {
            return "'$vars'";
        }

        if (is_numeric($vars)) {
            return (string)$vars;
        }

        if (is_null($vars)) {
            return 'null';
        }

        if (is_bool($vars)) {
            return $vars ? 'true' : 'false';
        }

        if (is_array($vars)) {
            return self::fromArray($vars, $space, $inline);
        }

        return 'UNKNOW DATA TYPE';
    }
}
