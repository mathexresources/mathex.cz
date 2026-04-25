<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class SettingsRepository
{
    private const TABLE = 'site_settings';

    public function __construct(
        private readonly Explorer $db,
    ) {
    }

    private function getTable(): Selection
    {
        return $this->db->table(self::TABLE);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->getTable()->where('key', $key)->fetch();
        return $row ? $row->value : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $existing = $this->getTable()->where('key', $key)->fetch();
        if ($existing) {
            $this->getTable()->where('key', $key)->update(['value' => $value]);
        } else {
            $this->getTable()->insert(['key' => $key, 'value' => $value]);
        }
    }

    public function getGroup(string $group): array
    {
        $rows = $this->getTable()->where('group', $group)->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->key] = $row->value;
        }
        return $result;
    }

    public function getAll(): array
    {
        $rows = $this->getTable()->order('`group` ASC, `key` ASC')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->key] = $row->value;
        }
        return $result;
    }

    public function getAllGrouped(): array
    {
        $rows = $this->getTable()->order('`group` ASC, `key` ASC')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->group][$row->key] = $row;
        }
        return $result;
    }

    public function findAll(): Selection
    {
        return $this->getTable()->order('`group` ASC, `key` ASC');
    }

    public function findByKey(string $key): ?ActiveRow
    {
        return $this->getTable()->where('key', $key)->fetch() ?: null;
    }

    public function delete(string $key): int
    {
        return $this->getTable()->where('key', $key)->delete();
    }
}
