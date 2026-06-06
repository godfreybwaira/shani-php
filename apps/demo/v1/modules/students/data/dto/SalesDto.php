<?php

/**
 * Description of SalesDto
 * @author goddy
 *
 * @since v1.0: Jun 6, 2026 at 11:27:07 AM
 */

namespace apps\demo\v1\modules\students\data\dto {

    final class SalesDto implements \JsonSerializable
    {

        public readonly int $id;
        public readonly \DateTimeImmutable $date;
        public readonly string $customer;
        public readonly string $product;
        public readonly int $quantity;
        public readonly float $price;
        public readonly float $total;

        public function __construct(
                int $id,
                \DateTimeImmutable $date,
                string $customer,
                string $product,
                int $quantity,
                float $price
        )
        {
            $this->id = $id;
            $this->date = $date;
            $this->customer = $customer;
            $this->product = $product;
            $this->quantity = $quantity;
            $this->price = $price;
            $this->total = $quantity * $price;
        }

        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'date' => $this->date->format('Y-m-d'),
                'customer' => $this->customer,
                'product' => $this->product,
                'quantity' => $this->quantity,
                'price' => $this->price,
            ];
        }
    }

}
