<?php

/**
 * Description of CreditCard
 * @author coder
 *
 * Created on: Sep 1, 2020 at 12:34:13 AM
 */

namespace library\validation {

    final class CreditCard
    {

        private const ERROR = 'is not valid credit card number';

        private static $cards = [
            'American Express' => [
                'name' => 'amex',
                'length' => [15],
                'prefixes' => [34, 37],
                'checkdigit' => true,
            ],
            'China UnionPay' => [
                'name' => 'unionpay',
                'length' => [16, 17, 18, 19],
                'prefixes' => [62],
                'checkdigit' => true,
            ],
            'Dankort' => [
                'name' => 'dankort',
                'length' => [16],
                'prefixes' => [5019, 4175, 4571, 4],
                'checkdigit' => true,
            ],
            'DinersClub' => [
                'name' => 'dinersclub',
                'length' => [14, 16],
                'prefixes' => [300, 301, 302, 303, 304, 305, 309, 36, 38, 39, 54, 55],
                'checkdigit' => true,
            ],
            'DinersClub CarteBlanche' => [
                'name' => 'carteblanche',
                'length' => [14],
                'prefixes' => [300, 301, 302, 303, 304, 305],
                'checkdigit' => true,
            ],
            'Discover Card' => [
                'name' => 'discover',
                'length' => [16, 19],
                'prefixes' => [6011, 622, 644, 645, 656, 647, 648, 649, 65],
                'checkdigit' => true,
            ],
            'InterPayment' => [
                'name' => 'interpayment',
                'length' => [16, 17, 18, 19],
                'prefixes' => [4],
                'checkdigit' => true,
            ],
            'JCB' => [
                'name' => 'jcb',
                'length' => [16, 17, 18, 19],
                'prefixes' => [352, 353, 354, 355, 356, 357, 358],
                'checkdigit' => true,
            ],
            'Maestro' => [
                'name' => 'maestro',
                'length' => [12, 13, 14, 15, 16, 18, 19],
                'prefixes' => [50, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69],
                'checkdigit' => true,
            ],
            'MasterCard' => [
                'name' => 'mastercard',
                'length' => [16],
                'prefixes' => [51, 52, 53, 54, 55, 22, 23, 24, 25, 26, 27],
                'checkdigit' => true,
            ],
            'NSPK MIR' => [
                'name' => 'mir',
                'length' => [16],
                'prefixes' => [2200, 2201, 2202, 2203, 2204],
                'checkdigit' => true,
            ],
            'Troy' => [
                'name' => 'troy',
                'length' => [16],
                'prefixes' => [979200, 979289],
                'checkdigit' => true,
            ],
            'UATP' => [
                'name' => 'uatp',
                'length' => [15],
                'prefixes' => [1],
                'checkdigit' => true,
            ],
            'Verve' => [
                'name' => 'verve',
                'length' => [16, 19],
                'prefixes' => [506, 650],
                'checkdigit' => true,
            ],
            'Visa' => [
                'name' => 'visa',
                'length' => [13, 16, 19],
                'prefixes' => [4],
                'checkdigit' => true,
            ],
            'BMO ABM Card' => [
                'name' => 'bmoabm',
                'length' => [16],
                'prefixes' => [500],
                'checkdigit' => false,
            ],
            'CIBC Convenience Card' => [
                'name' => 'cibc',
                'length' => [16],
                'prefixes' => [4506],
                'checkdigit' => false,
            ],
            'HSBC Canada Card' => [
                'name' => 'hsbc',
                'length' => [16],
                'prefixes' => [56],
                'checkdigit' => false,
            ],
            'Royal Bank of Canada Client Card' => [
                'name' => 'rbc',
                'length' => [16],
                'prefixes' => [45],
                'checkdigit' => false,
            ],
            'Scotiabank Scotia Card' => [
                'name' => 'scotia',
                'length' => [16],
                'prefixes' => [4536],
                'checkdigit' => false,
            ],
            'TD Canada Trust Access Card' => [
                'name' => 'tdtrust',
                'length' => [16],
                'prefixes' => [589297],
                'checkdigit' => false,
            ],
        ];

        public static function check(string $ccNumber, string $type): ?string
        {
            $type = strtolower($type);
            $info = null;

            // Get our card info based on provided name.
            foreach (static::$cards as $card) {
                if ($card['name'] === $type) {
                    $info = $card;
                    break;
                }
            }

            // If empty, it's not a card type we recognize, or invalid type.
            if (empty($info)) {
                return self::ERROR;
            }

            // Remove any spaces and dashes
            $ccNumber = str_replace([' ', '-'], '', $ccNumber);

            // Non-numeric values cannot be a number
            // Make sure it's a valid length for this card
            if (!is_numeric($ccNumber) || !in_array(strlen($ccNumber), $info['length'])) {
                return self::ERROR;
            }

            // Make sure it has a valid prefix
            $validPrefix = false;
            foreach ($info['prefixes'] as $prefix) {
                if (str_starts_with($ccNumber, (string) $prefix)) {
                    $validPrefix = true;
                    break;
                }
            }

            if ($validPrefix === false) {
                return self::ERROR;
            }

            if ($info['checkdigit'] === true) {
                return self::isValidLuhn($ccNumber) ? null : self::ERROR;
            }
            return null;
        }

        private static function isValidLuhn(string $number = null): bool
        {
            settype($number, 'string');
            $sumTable = [
                [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,],
                [0, 2, 4, 6, 8, 1, 3, 5, 7, 9,],
            ];
            $sum = $flip = 0;
            for ($i = strlen($number) - 1; $i >= 0; $i--) {
                $sum += $sumTable[$flip++ & 0x1][$number[$i]];
            }
            return $sum % 10 === 0;
        }

        public static function getName(string $ccNumber): ?array
        {
            $possibles = null;
            $length = strlen(str_replace([' ', '-'], '', $ccNumber));
            foreach (static::$cards as $key => $card) {
                if (!in_array($length, $card['length'])) {
                    continue;
                }
                foreach ($card['prefixes'] as $prefix) {
                    if (str_starts_with($ccNumber, (string) $prefix)) {
                        $possibles[$card['name']] = $key;
                    }
                }
            }
            return $possibles;
        }
    }

}
