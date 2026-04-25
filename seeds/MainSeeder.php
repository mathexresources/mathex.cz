<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Orchestrates all seeders in dependency order.
 * Run with: make db-seed  (or: vendor/bin/phinx seed:run -s MainSeeder)
 *
 * Individual seeders can also be run directly:
 *   vendor/bin/phinx seed:run -s UserSeeder
 */
final class MainSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->output->writeln('');
        $this->output->writeln('<comment>  ── Seeding database ─────────────────────────────────────────</comment>');

        $seeders = [
            'UserSeeder',
            'SiteSettingsSeeder',
            'SkillSeeder',
            'ServiceSeeder',
            'PricingPlanSeeder',
            'ProjectSeeder',
            'TestimonialSeeder',
            'BlogPostSeeder',
        ];

        foreach ($seeders as $name) {
            /** @var AbstractSeed $seeder */
            $seeder = new $name();
            $seeder->setAdapter($this->getAdapter());
            $seeder->setOutput($this->output);
            $seeder->run();
        }

        $this->output->writeln('<comment>  ── Done ─────────────────────────────────────────────────────</comment>');
        $this->output->writeln('');
        $this->output->writeln('  Admin login: admin@mathex.cz / Admin1234!');
        $this->output->writeln('  <fg=yellow>⚠  Change the admin password before going to production!</fg=yellow>');
        $this->output->writeln('');
    }
}
