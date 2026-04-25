<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\BlogCommentRepository;
use App\Model\Database\BlogPostRepository;
use App\Model\Database\NewsletterRepository;
use App\Model\Service\MarkdownService;
use App\Model\Service\NewsletterService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

final class BlogPresenter extends BasePresenter
{
    private const POSTS_PER_PAGE = 9;

    public function __construct(
        private readonly BlogPostRepository  $blogPosts,
        private readonly BlogCommentRepository $comments,
        private readonly NewsletterRepository $newsletter,
        private readonly NewsletterService   $newsletterService,
        private readonly MarkdownService     $markdown,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $page   = max(1, (int) $this->getParameter('page'));
        $search = (string) ($this->getParameter('search') ?? '');
        $tag    = (string) ($this->getParameter('tag') ?? '');

        if ($search !== '') {
            $selection = $this->blogPosts->searchFulltext($search);
        } elseif ($tag !== '') {
            $selection = $this->blogPosts->findByTag($tag);
        } else {
            $selection = $this->blogPosts->findPublished();
        }

        $total     = (clone $selection)->count('*');
        $pageCount = $total > 0 ? (int) ceil($total / self::POSTS_PER_PAGE) : 1;
        $page      = min($page, $pageCount);
        $posts     = (clone $selection)->limit(self::POSTS_PER_PAGE, ($page - 1) * self::POSTS_PER_PAGE);

        $this->template->pageTitle       = $this->translator->translate('blog.page_title');
        $this->template->posts           = $posts;
        $this->template->page            = $page;
        $this->template->pageCount       = $pageCount;
        $this->template->total           = $total;
        $this->template->search          = $search;
        $this->template->activeTag       = $tag;
        $this->template->allTags         = $this->blogPosts->getAllTags();
        $this->template->subscriberCount = $this->newsletter->getConfirmedCount();

        $this->metaDescription = $this->translator->translate('blog.meta_desc');
        $this->canonicalUrl    = $this->link('//Blog:default');
    }

    public function renderDetail(string $slug): void
    {
        $post    = $this->blogPosts->findBySlug($slug);
        $preview = (bool) $this->getParameter('preview');

        if (!$post) {
            throw new BadRequestException('Článek nenalezen.', 404);
        }

        // Preview mode: allow admin to view drafts
        if ($post->status !== 'published') {
            if (!$preview || !$this->getUser()->isLoggedIn()) {
                throw new BadRequestException('Článek nenalezen.', 404);
            }
        }

        if ($post->status === 'published') {
            $this->blogPosts->incrementViews($post->id);
        }

        $contentRaw  = (string) ($post->{'content_' . $this->locale} ?? $post->content_cs ?? $post->content_en ?? '');
        $postContent = $this->markdown->toBlogHtml($contentRaw);

        $wordCount   = str_word_count(strip_tags($contentRaw));
        $readingTime = $post->reading_time_min ?? max(1, (int) ceil($wordCount / 200));

        $tags = $post->tags ? json_decode((string) $post->tags, true) : [];

        $relatedPosts = $this->blogPosts->findRelatedByTags((int) $post->id, (array) $tags, 3);

        $prevPost = $this->blogPosts->findPublished()
            ->where('published_at < ?', $post->published_at)
            ->order('published_at DESC')
            ->limit(1)
            ->fetch();

        $nextPost = $this->blogPosts->findPublished()
            ->where('published_at > ?', $post->published_at)
            ->order('published_at ASC')
            ->limit(1)
            ->fetch();

        $approvedComments = $this->comments->findApprovedForPost((int) $post->id);

        // Set comment form default for post_id
        $this['commentForm']['post_id']->setDefaultValue($post->id);

        $this->template->pageTitle        = $post->{'title_' . $this->locale} ?? $post->title_cs ?? $post->title_en ?? $slug;
        $this->template->post             = $post;
        $this->template->postContent      = $postContent;
        $this->template->readingTime      = $readingTime;
        $this->template->tags             = $tags;
        $this->template->relatedPosts     = $relatedPosts;
        $this->template->prevPost         = $prevPost ?: null;
        $this->template->nextPost         = $nextPost ?: null;
        $this->template->comments         = $approvedComments;
        $this->template->commentCount     = $approvedComments->count('*');
        $this->template->previewMode      = $preview;

        $this->metaDescription = (string) ($post->{'perex_' . $this->locale} ?? $post->perex_cs ?? $post->perex_en ?? $this->metaDescription);
        $this->ogImage         = $post->cover_image ?? null;
        $this->canonicalUrl    = $this->link('//Blog:detail', $slug);
        $this->jsonLd = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $this->template->pageTitle,
            'description'      => $this->metaDescription,
            'image'            => $this->ogImage,
            'datePublished'    => $post->published_at ? date('c', strtotime((string) $post->published_at)) : null,
            'dateModified'     => $post->updated_at ? date('c', strtotime((string) $post->updated_at)) : null,
            'author'           => ['@type' => 'Person', 'name' => 'Mathex'],
            'publisher'        => ['@type' => 'Organization', 'name' => $this->siteTitle],
            'timeRequired'     => 'PT' . $readingTime . 'M',
            'keywords'         => implode(', ', (array) $tags),
            'url'              => $this->canonicalUrl,
        ];
    }

    public function renderTag(string $tag): void
    {
        $page      = max(1, (int) ($this->getParameter('page') ?? 1));
        $selection = $this->blogPosts->findByTag($tag);
        $total     = (clone $selection)->count('*');
        $pageCount = $total > 0 ? (int) ceil($total / self::POSTS_PER_PAGE) : 1;
        $page      = min($page, $pageCount);
        $posts     = (clone $selection)->limit(self::POSTS_PER_PAGE, ($page - 1) * self::POSTS_PER_PAGE);

        $this->template->pageTitle  = sprintf($this->translator->translate('blog.tag_title'), $tag);
        $this->template->posts      = $posts;
        $this->template->tag        = $tag;
        $this->template->page       = $page;
        $this->template->pageCount  = $pageCount;
        $this->template->total      = $total;
        $this->template->search     = '';
        $this->template->activeTag  = $tag;
        $this->template->allTags    = $this->blogPosts->getAllTags();
        $this->template->subscriberCount = $this->newsletter->getConfirmedCount();

        $this->metaDescription = sprintf($this->translator->translate('blog.tag_meta_desc'), $tag);
    }

    public function renderFeed(): void
    {
        $this->getHttpResponse()->setContentType('application/rss+xml', 'UTF-8');
        $this->setLayout(false);

        $posts = $this->blogPosts->findPublished()->limit(20);

        $this->template->posts    = $posts;
        $this->template->siteUrl  = rtrim($this->link('//Homepage:default'), '/');
        $this->template->feedUrl  = $this->link('//Blog:feed');
        $this->template->blogUrl  = $this->link('//Blog:default');
        $this->template->siteTitle = $this->siteTitle;
        $this->template->buildDate = date(DATE_RSS);
    }

    protected function createComponentCommentForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte své jméno.')
            ->setMaxLength(255);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte svůj e-mail.')
            ->setMaxLength(255);

        $form->addTextArea('content', 'Komentář:')
            ->setRequired('Napište komentář.')
            ->setMaxLength(3000)
            ->setHtmlAttribute('rows', 5);

        // Honeypot
        $form->addText('website', '')
            ->setHtmlAttribute('tabindex', '-1')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addHidden('post_id');

        $form->addSubmit('submit', 'Odeslat komentář');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

    public function commentFormSucceeded(Form $form, \stdClass $values): void
    {
        if ($values->website !== '') {
            // Honeypot triggered
            $this->redirect('this');
        }

        $post = $this->blogPosts->find((int) $values->post_id);
        if (!$post) {
            $this->redirect('Blog:default');
        }

        $this->comments->insert([
            'post_id'    => (int) $values->post_id,
            'name'       => $values->name,
            'email'      => $values->email,
            'content'    => $values->content,
            'ip_address' => $this->getHttpRequest()->getRemoteAddress(),
        ]);

        $this->flashMessage('Komentář byl odeslán ke schválení. Děkujeme!', 'success');
        $this->redirect('this');
    }
}
