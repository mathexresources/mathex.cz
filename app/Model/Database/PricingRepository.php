<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class PricingRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'pricing_plans';
    }

    public function findByService(int $serviceId): Selection
    {
        return $this->getTable()
            ->where('service_id', $serviceId)
            ->order('order_index ASC');
    }

    public function findActive(): Selection
    {
        return $this->getTable()
            ->where('active', 1)
            ->order('service_id ASC, order_index ASC');
    }

    public function findAllOrdered(): Selection
    {
        return $this->getTable()->order('service_id ASC, order_index ASC');
    }
}
