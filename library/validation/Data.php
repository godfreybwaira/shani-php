<?php

/**
 * Description of Data
 * @author coder
 *
 * Created on: Mar 4, 2024 at 9:56:18 AM
 */

namespace library\validation {

    final class Data {

        public static function validate(array &$inputs, array $constraints): ?string {
            foreach ($constraints as $name => $constraint) {
                if (!isset($inputs[$name])) {
                    return $name . ' column not set';
                }
                $key = -1;
                if ($constraint === null) {
                    continue;
                }
                foreach ($constraint['rules'] as $classMethod => $params) {
                    ++$key;
                    if ($params === null) {
                        $msg = $classMethod($inputs[$name]);
                    } else {
                        $msg = $classMethod(...[$inputs[$name], $params]);
                    }
                    if ($msg !== null) {
                        return $constraint['messages'][$key] ?? ($constraint['label'] ?? $name) . ' ' . $msg;
                    }
                }
            }
            return null;
        }
    }

}
