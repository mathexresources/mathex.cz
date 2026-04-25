<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

abstract class Repository
{
    public function __construct(
        protected readonly Explorer $db,
    ) {
    }

    abstract protected function getTableName(): string;

    public function getTable(): Selection
    {
        return $this->db->table($this->getTableName());
    }

    public function findAll(): Selection
    {
        return $this->getTable();
    }

    public function find(int $id): ?ActiveRow
    {
        return $this->getTable()->get($id) ?: null;
    }

    public function findById(int $id): ?ActiveRow
    {
        return $this->find($id);
    }

    public function findBySlug(string $slug): ?ActiveRow
    {
        return $this->getTable()->where('slug', $slug)->fetch() ?: null;
    }

    public function findBy(array $conditions): Selection
    {
        return $this->getTable()->where($conditions);
    }

    public function insert(array $data): ActiveRow|int|bool
    {
        return $this->getTable()->insert($data);
    }

    public function update(int $id, array $data): int
    {
        return $this->getTable()->where('id', $id)->update($data);
    }

    public function delete(int $id): int
    {
        return $this->getTable()->where('id', $id)->delete();
    }

    public function save(array $data): ActiveRow|int|bool
    {
        if (isset($data['id']) && $data['id']) {
            $id = (int) $data['id'];
            unset($data['id']);
            $this->update($id, $data);
            return $this->find($id) ?? true;
        }
        return $this->insert($data);
    }

    public function count(array $conditions = []): int
    {
        $selection = $this->getTable();
        if ($conditions) {
            $selection->where($conditions);
        }
        return $selection->count('*');
    }

    /**
     * @return array{items: Selection, total: int, page: int, itemsPerPage: int, pageCount: int}
     */
    public function paginate(int $page, int $itemsPerPage = 10, array $conditions = []): array
    {
        $selection = $this->getTable();
        if ($conditions) {
            $selection->where($conditions);
        }
        $total = (clone $selection)->count('*');
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);

        return [
            'items' => $selection,
            'total' => $total,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'pageCount' => $total > 0 ? (int) ceil($total / $itemsPerPage) : 0,
        ];
    }
}
