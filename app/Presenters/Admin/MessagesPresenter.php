<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\ContactRepository;

final class MessagesPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ContactRepository $contacts,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $filter = 'all', int $page = 1): void
    {
        $this->template->pageTitle    = 'Zprávy';
        $this->template->activeFilter = $filter;

        $result = match ($filter) {
            'unread' => $this->contacts->paginate($page, 20, ['is_read' => 0]),
            'read'   => $this->contacts->paginate($page, 20, ['is_read' => 1]),
            default  => $this->contacts->paginate($page, 20),
        };

        $result['items']->order('created_at DESC');

        $this->template->messages  = $result['items'];
        $this->template->paginator = $result;
    }

    public function actionRead(int $id): void
    {
        $this->contacts->find($id) ?? $this->error('Zpráva nenalezena.', 404);
        $this->contacts->markRead($id);
        $this->redirect('default');
    }

    public function actionUnread(int $id): void
    {
        $this->contacts->find($id) ?? $this->error('Zpráva nenalezena.', 404);
        $this->contacts->update($id, ['is_read' => 0]);
        $this->redirect('default');
    }

    public function actionDelete(int $id): void
    {
        $this->contacts->find($id) ?? $this->error('Zpráva nenalezena.', 404);
        $this->contacts->delete($id);
        $this->flashMessage('Zpráva byla smazána.', 'success');
        $this->redirect('default');
    }
}
