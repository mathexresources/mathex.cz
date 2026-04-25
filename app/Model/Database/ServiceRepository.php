<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class ServiceRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'services';
    }

    public function findActive(): Selection
    {
        return $this->getTable()
            ->where('active', 1)
            ->order('order_index ASC');
    }

    public function findBySlug(string $slug): ?ActiveRow
    {
        return $this->getTable()->where('slug', $slug)->fetch() ?: null;
    }

    /**
     * Returns active services; pricing plans are accessible via $service->related('pricing_plans').
     * Each item in the returned array has 'service' and 'plans' keys.
     *
     * @return list<array{service: ActiveRow, plans: list<ActiveRow>}>
     */
    public function findWithPricing(): array
    {
        $result = [];
        foreach ($this->findActive() as $service) {
            $plans = $service->related('pricing_plans.service_id')
                ->where('active', 1)
                ->order('order_index ASC')
                ->fetchAll();
            $result[] = [
                'service' => $service,
                'plans' => array_values($plans),
            ];
        }
        return $result;
    }
}
