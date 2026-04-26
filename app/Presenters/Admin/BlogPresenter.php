<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\BlogCommentRepository;
use App\Model\Database\BlogPostRepository;
use App\Model\Service\ImageUploadService;
use App\Model\Service\MarkdownService;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

final class BlogPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly BlogPostRepository   $blogPosts,
        private readonly BlogCommentRepository $blogComments,
        private readonly ImageUploadService   $imageUpload,
        private readonly MarkdownService      $markdown,
    ) {
        parent::__construct();
    }

    public function renderDefault(int $page = 1, string $search = '', string $status = ''): void
    {
        $this->template->pageTitle    = 'Blog';
        $this->template->search       = $search;
        $this->template->statusFilter = $status;
        $this->template->pendingComments = $this->blogComments->countPending();

        $conditions = [];
        if ($status !== '') {
            $conditions['status'] = $status;
        }

        $result = $this->blogPosts->paginate($page, 20, $conditions);

        if ($search !== '') {
            $like = "%{$search}%";
            $result['items']->where(
                'title_cs LIKE ? OR title_en LIKE ? OR perex_cs LIKE ?',
                $like, $like, $like,
            );
            $result['total']     = (clone $result['items'])->count('*');
            $result['pageCount'] = (int) ceil($result['total'] / 20);
        }

        $result['items']->order('created_at DESC');
        $this->template->posts     = $result['items'];
        $this->template->paginator = $result;
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle  = $id ? 'Upravit článek' : 'Nový článek';
        $this->template->postId     = $id;
        $this->template->existingTags    = $this->blogPosts->getAllTags();
        $this->template->existingTagsJson = json_encode($this->blogPosts->getAllTags());
        $this->template->coverImage = null;

        if ($id) {
            $post = $this->blogPosts->find($id) ?? $this->error('Článek nenalezen.', 404);
            $this->template->coverImage = $post->cover_image;
            $defaults = (array) $post;
            $defaults['tags'] = is_string($post->tags)
                ? implode(', ', json_decode($post->tags, true) ?? [])
                : '';
            $defaults['published_at'] = $post->published_at
                ? date('Y-m-d\TH:i', strtotime((string) $post->published_at))
                : '';
            $this['blogForm']->setDefaults($defaults);
        }
    }

    public function renderComments(int $page = 1, string $filter = 'pending'): void
    {
        $this->template->pageTitle = 'Komentáře';
        $this->template->filter    = $filter;

        $selection = match ($filter) {
            'approved' => $this->blogComments->getTable()->where('status', 'approved'),
            'rejected' => $this->blogComments->getTable()->where('status', 'rejected'),
            default    => $this->blogComments->findPending(),
        };

        $result = [
            'items'     => (clone $selection)->order('created_at DESC')->limit(30, ($page - 1) * 30),
            'total'     => $selection->count('*'),
            'page'      => $page,
            'pageCount' => (int) ceil($selection->count('*') / 30),
        ];

        $this->template->comments  = $result['items'];
        $this->template->paginator = $result;
    }

    public function actionDelete(int $id): void
    {
        $this->blogPosts->find($id) ?? $this->error('Článek nenalezen.', 404);
        $this->blogPosts->delete($id);
        $this->flashMessage('Článek byl smazán.', 'success');
        $this->redirect('default');
    }

    public function actionToggleStatus(int $id): void
    {
        $post      = $this->blogPosts->find($id) ?? $this->error('Článek nenalezen.', 404);
        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        $data      = ['status' => $newStatus];
        if ($newStatus === 'published' && !$post->published_at) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        $this->blogPosts->update($id, $data);
        $this->redirect('default');
    }

    public function actionBulk(): void
    {
        $action = $this->getRequest()->getPost('bulk_action');
        $ids    = array_map('intval', (array) $this->getRequest()->getPost('ids', []));

        if (!$ids) {
            $this->redirect('default');
        }

        foreach ($ids as $id) {
            match ($action) {
                'publish' => $this->blogPosts->update($id, ['status' => 'published', 'published_at' => date('Y-m-d H:i:s')]),
                'archive' => $this->blogPosts->update($id, ['status' => 'archived']),
                'delete'  => $this->blogPosts->delete($id),
                default   => null,
            };
        }

        $this->flashMessage('Hromadná akce provedena.', 'success');
        $this->redirect('default');
    }

    public function actionApproveComment(int $id): void
    {
        $this->blogComments->approve($id);
        $this->flashMessage('Komentář byl schválen.', 'success');
        $this->redirect('comments');
    }

    public function actionRejectComment(int $id): void
    {
        $this->blogComments->reject($id);
        $this->flashMessage('Komentář byl zamítnut.', 'success');
        $this->redirect('comments');
    }

    public function actionDeleteComment(int $id): void
    {
        $this->blogComments->delete($id);
        $this->flashMessage('Komentář byl smazán.', 'success');
        $this->redirect('comments');
    }

    /** JSON endpoint: returns all existing tags for autocomplete. */
    public function actionTags(): void
    {
        $this->getHttpResponse()->setContentType('application/json', 'UTF-8');
        $this->sendJson($this->blogPosts->getAllTags());
    }

    /** Save draft and redirect to public preview. */
    public function actionPreview(int $id): void
    {
        $post = $this->blogPosts->find($id) ?? $this->error('Článek nenalezen.', 404);
        $this->redirectUrl($this->link('//Blog:detail', ['slug' => $post->slug, 'preview' => 1]));
    }

    protected function createComponentBlogForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('title_cs', 'Nadpis (CS):')
            ->setRequired('Zadejte nadpis.')
            ->setMaxLength(500)
            ->setHtmlAttribute('id', 'title_cs');

        $form->addText('title_en', 'Nadpis (EN):')
            ->setMaxLength(500);

        $form->addText('slug', 'Slug (URL):')
            ->setMaxLength(255)
            ->setHtmlAttribute('id', 'slug');

        $form->addTextArea('perex_cs', 'Perex (CS):')
            ->setMaxLength(1000)
            ->setHtmlAttribute('rows', 3);

        $form->addTextArea('perex_en', 'Perex (EN):')
            ->setMaxLength(1000)
            ->setHtmlAttribute('rows', 3);

        $form->addTextArea('content_cs', 'Obsah (CS, Markdown):')
            ->setHtmlAttribute('rows', 20)
            ->setHtmlAttribute('class', 'form-control markdown-editor')
            ->setHtmlAttribute('id', 'content_cs');

        $form->addTextArea('content_en', 'Obsah (EN, Markdown):')
            ->setHtmlAttribute('rows', 20)
            ->setHtmlAttribute('class', 'form-control markdown-editor')
            ->setHtmlAttribute('id', 'content_en');

        $form->addUpload('cover_image_file', 'Cover obrázek (1200×630):')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Povoleny jsou JPEG, PNG, GIF a WebP.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost je 10 MB.', 10 * 1024 * 1024);

        $form->addText('tags', 'Tagy:')
            ->setMaxLength(500)
            ->setHtmlAttribute('id', 'tags-input');

        $form->addSelect('status', 'Stav:', [
            'draft'     => 'Koncept',
            'published' => 'Publikováno',
            'archived'  => 'Archivováno',
        ])->setDefaultValue('draft');

        $form->addText('published_at', 'Datum publikace:')
            ->setHtmlType('datetime-local');

        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('name', '_submit')
            ->setHtmlAttribute('value', 'save');

        $form->addSubmit('preview', 'Uložit a náhled →')
            ->setHtmlAttribute('name', '_submit')
            ->setHtmlAttribute('value', 'preview')
            ->setValidationScope([]);

        $form->onSuccess[] = [$this, 'blogFormSucceeded'];

        return $form;
    }

    public function blogFormSucceeded(Form $form, \stdClass $values): void
    {
        $id       = (int) $this->getParameter('id');
        $isPreview = $form->isSubmitted() === $form['preview'];

        if (empty($values->slug)) {
            $values->slug = Strings::webalize($values->title_cs);
        }

        $readingTime = max(1, $this->markdown->estimateReadingTime(
            (string) ($values->content_cs ?: $values->content_en ?: ''),
        ));

        $data = [
            'title_cs'        => $values->title_cs,
            'title_en'        => $values->title_en,
            'slug'            => $values->slug,
            'perex_cs'        => $values->perex_cs,
            'perex_en'        => $values->perex_en,
            'content_cs'      => $values->content_cs,
            'content_en'      => $values->content_en,
            'status'          => $values->status,
            'reading_time_min' => $readingTime,
            'published_at'    => $values->published_at
                ? date('Y-m-d H:i:s', strtotime($values->published_at))
                : null,
            'tags'            => json_encode(
                array_values(array_filter(array_map('trim', explode(',', $values->tags)))),
            ),
        ];

        if (!$id) {
            $data['author_id'] = $this->getUser()->getId();
        }

        if ($values->cover_image_file->isOk()) {
            try {
                $url             = $this->imageUpload->uploadBlogCover($values->cover_image_file);
                $data['cover_image'] = $url;
            } catch (\RuntimeException $e) {
                $form->addError('Chyba při nahrávání obrázku: ' . $e->getMessage());
                return;
            }
        }

        if ($id) {
            $this->blogPosts->update($id, $data);
            $savedId = $id;
            $this->flashMessage('Článek byl uložen.', 'success');
        } else {
            $row     = $this->blogPosts->insert($data);
            $savedId = (int) $row->getPrimary();
            $this->flashMessage('Článek byl přidán.', 'success');
        }

        if ($isPreview) {
            $this->redirect('preview', $savedId);
        }

        $this->redirect('default');
    }
}
