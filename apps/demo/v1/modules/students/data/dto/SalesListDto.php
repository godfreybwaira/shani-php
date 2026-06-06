<?php

/**
 * Description of SalesListDto
 * @author goddy
 *
 * @since v1.0: Jun 6, 2026 at 5:04:17 PM
 */

namespace apps\demo\v1\modules\students\data\dto {

    final class SalesListDto implements \JsonSerializable
    {

        /** @var SalesDto[] */
        private array $sales;

        public function __construct(array $sales)
        {
            foreach ($sales as $sale) {
                if (!$sale instanceof \JsonSerializable) {
                    throw new \InvalidArgumentException("All items must be instances of SalesDto");
                }
            }
            $this->sales = $sales;
        }

        public function jsonSerialize(): array
        {
            return $this->sales;
        }
    }

}
