<?php
declare(strict_types=1);

namespace BSG\Clients\Contracts;

interface ApiClientContract
{
    public function getStatusById(int $message_id): array;

    public function getStatusByReference(string $reference): array;

    public function getPrices(?string $tariff = null): array;
}