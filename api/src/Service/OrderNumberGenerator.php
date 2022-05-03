<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class OrderNumberGenerator
{
    private const NUMBER_PADDING_LENGTH = 4;

    public static function generateOrderNumber(EntityManagerInterface $entityManager): string
    {
        $nextOrderCount = $entityManager->getRepository(Order::class)->getCountByDate() + 1;

        return date('ymd') .
            str_pad($nextOrderCount, self::NUMBER_PADDING_LENGTH, '0', STR_PAD_LEFT);
    }
}
