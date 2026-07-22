<?php

/**
 * Description of ValidLogin
 * @author goddy
 *
 * @since v1.0: Jul 22, 2026 at 5:21:21 PM
 */
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace apps\demo\v1\modules\oauth2\logic\controllers\post {

    final class ValidLogin implements \features\validation\ValidationInterface
    {

//put your code here
        public function validate(string|int $key, mixed $value): ?string
        {
            if ($key === 'age') {
                return $value > 18 ? 'No adult' : null;
            }
            return 'failed. try again';
        }
    }

}
