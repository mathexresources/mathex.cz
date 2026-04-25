<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

final class AnalyticsPresenter extends BaseAdminPresenter
{
    public function renderDefault(string $range = '30'): void
    {
        $this->template->pageTitle    = 'Analytika';
        $this->template->activeRange  = $range;

        $days = match ($range) {
            '7'  => 7,
            '90' => 90,
            default => 30,
        };

        $from = new \DateTimeImmutable("-{$days} days");
        $to   = new \DateTimeImmutable();

        $this->template->visitorStats = $this->analytics->getVisitorStats($from, $to);
        $this->template->topPages     = $this->analytics->getTopPages(15, $from, $to);
        $this->template->referrers    = $this->analytics->getReferrerStats(10);
        $this->template->totalViews   = $this->analytics->getPageViews($from, $to);
        $this->template->rangeFrom    = $from;
        $this->template->rangeTo      = $to;
    }
}
