<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\NewsletterRepository;
use App\Model\Service\MarkdownService;
use App\Model\Service\NewsletterService;
use Nette\Application\UI\Form;

final class NewsletterPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly NewsletterRepository $newsletter,
        private readonly NewsletterService    $newsletterService,
        private readonly MarkdownService      $markdown,
    ) {
        parent::__construct();
    }

    public function renderDefault(int $page = 1, string $filter = ''): void
    {
        $this->template->pageTitle      = 'Newsletter';
        $this->template->filter         = $filter;
        $this->template->confirmedCount = $this->newsletter->getConfirmedCount();
        $this->template->totalCount     = $this->newsletter->getTable()->count('*');

        $selection = match ($filter) {
            'confirmed'   => $this->newsletter->findConfirmed(),
            'unconfirmed' => $this->newsletter->getTable()->where('confirmed', false),
            default       => $this->newsletter->findAll(),
        };

        $perPage   = 50;
        $total     = $selection->count('*');
        $pageCount = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $this->template->subscribers = (clone $selection)->limit($perPage, ($page - 1) * $perPage);
        $this->template->paginator   = [
            'page'      => $page,
            'pageCount' => $pageCount,
            'total'     => $total,
        ];
    }

    public function renderSend(): void
    {
        $this->template->pageTitle      = 'Odeslat newsletter';
        $this->template->confirmedCount = $this->newsletter->getConfirmedCount();
    }

    public function actionDeleteSubscriber(int $id): void
    {
        $this->newsletter->delete($id);
        $this->flashMessage('Odběratel byl smazán.', 'success');
        $this->redirect('default');
    }

    protected function createComponentSendForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('subject', 'Předmět:')
            ->setRequired('Zadejte předmět.')
            ->setMaxLength(255);

        $form->addTextArea('body', 'Obsah (Markdown):')
            ->setRequired('Napište obsah newsletteru.')
            ->setHtmlAttribute('rows', 20)
            ->setHtmlAttribute('class', 'form-control markdown-editor')
            ->setHtmlAttribute('id', 'newsletter_body');

        $form->addSubmit('send', 'Odeslat všem odběratelům');

        $form->onSuccess[] = [$this, 'sendFormSucceeded'];

        return $form;
    }

    public function sendFormSucceeded(Form $form, \stdClass $values): void
    {
        $htmlBody = $this->markdown->toBlogHtml($values->body);

        // Append unsubscribe footer
        $htmlBody .= '<hr style="margin:2rem 0;border:1px solid #eee;">'
            . '<p style="font-size:0.8125rem;color:#888;">'
            . 'Odhlásit se z odběru: <a href="{{unsubscribe_url}}">klikněte zde</a>'
            . '</p>';

        try {
            $count = $this->newsletterService->broadcast($values->subject, $htmlBody);
            $this->flashMessage("Newsletter byl odeslán {$count} odběratelům.", 'success');
        } catch (\Exception $e) {
            $form->addError('Chyba při odesílání: ' . $e->getMessage());
            return;
        }

        $this->redirect('default');
    }
}
