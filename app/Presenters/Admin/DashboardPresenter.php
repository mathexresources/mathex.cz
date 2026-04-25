<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\BlogPostRepository;
use App\Model\Database\ContactRepository;
use App\Model\Database\MeetingRepository;
use App\Model\Database\ProjectRepository;

final class DashboardPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ProjectRepository  $projects,
        private readonly ContactRepository  $contacts,
        private readonly BlogPostRepository $blogPosts,
        private readonly MeetingRepository  $meetings,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Přehled';

        $this->template->projectCount  = $this->projects->count();
        $this->template->messageCount  = $this->contacts->count();
        $this->template->unreadCount   = $this->contacts->countUnread();
        $this->template->blogPostCount = $this->blogPosts->count();
        $this->template->meetingCount  = $this->meetings->count(['status' => 'pending']);

        $this->template->latestMessages    = $this->contacts->findAll()->order('created_at DESC')->limit(5);
        $this->template->upcomingMeetings  = $this->meetings->findPending()->limit(5);

        $from = new \DateTimeImmutable('-30 days');
        $to   = new \DateTimeImmutable();
        $this->template->visitorStats = $this->analytics->getVisitorStats($from, $to);
        $this->template->topPages     = $this->analytics->getTopPages(5, $from, $to);
        $this->template->pageViewsTotal = $this->analytics->getPageViews($from, $to);
    }
}
