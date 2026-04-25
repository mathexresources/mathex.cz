<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class SkillSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->table('skills')->truncate();

        $skills = [
            // ─── Backend ──────────────────────────────────────────────────────
            ['name' => 'PHP 8.x',           'level' => 96, 'category' => 'backend',  'icon_class' => 'devicon-php-plain',          'order_index' =>  1],
            ['name' => 'Nette Framework',    'level' => 93, 'category' => 'backend',  'icon_class' => 'devicon-nette-plain',         'order_index' =>  2],
            ['name' => 'MySQL / MariaDB',    'level' => 88, 'category' => 'backend',  'icon_class' => 'devicon-mysql-plain',         'order_index' =>  3],
            ['name' => 'Redis',              'level' => 82, 'category' => 'backend',  'icon_class' => 'devicon-redis-plain',         'order_index' =>  4],
            ['name' => 'REST API design',    'level' => 90, 'category' => 'backend',  'icon_class' => null,                          'order_index' =>  5],
            ['name' => 'PHPUnit / Tester',   'level' => 80, 'category' => 'backend',  'icon_class' => 'devicon-phpunit-plain',       'order_index' =>  6],
            ['name' => 'Composer',           'level' => 88, 'category' => 'backend',  'icon_class' => 'devicon-composer-plain',      'order_index' =>  7],
            ['name' => 'PHPStan / Psalm',    'level' => 82, 'category' => 'backend',  'icon_class' => null,                          'order_index' =>  8],
            // ─── Frontend ─────────────────────────────────────────────────────
            ['name' => 'HTML5 / CSS3',       'level' => 87, 'category' => 'frontend', 'icon_class' => 'devicon-html5-plain',         'order_index' =>  1],
            ['name' => 'JavaScript (ES2022)','level' => 81, 'category' => 'frontend', 'icon_class' => 'devicon-javascript-plain',    'order_index' =>  2],
            ['name' => 'Vue.js 3',           'level' => 78, 'category' => 'frontend', 'icon_class' => 'devicon-vuejs-plain',         'order_index' =>  3],
            ['name' => 'Alpine.js',          'level' => 83, 'category' => 'frontend', 'icon_class' => null,                          'order_index' =>  4],
            ['name' => 'Tailwind CSS',       'level' => 80, 'category' => 'frontend', 'icon_class' => 'devicon-tailwindcss-plain',   'order_index' =>  5],
            ['name' => 'Latte Templates',    'level' => 92, 'category' => 'frontend', 'icon_class' => null,                          'order_index' =>  6],
            // ─── DevOps ───────────────────────────────────────────────────────
            ['name' => 'Docker',             'level' => 86, 'category' => 'devops',   'icon_class' => 'devicon-docker-plain',        'order_index' =>  1],
            ['name' => 'Linux (Debian/Ubuntu)','level' => 82, 'category' => 'devops', 'icon_class' => 'devicon-linux-plain',         'order_index' =>  2],
            ['name' => 'Nginx',              'level' => 78, 'category' => 'devops',   'icon_class' => 'devicon-nginx-plain',         'order_index' =>  3],
            ['name' => 'Git / GitHub',       'level' => 93, 'category' => 'devops',   'icon_class' => 'devicon-git-plain',           'order_index' =>  4],
            ['name' => 'GitHub Actions',     'level' => 76, 'category' => 'devops',   'icon_class' => 'devicon-githubactions-plain', 'order_index' =>  5],
            ['name' => 'SSH / bash skripty', 'level' => 74, 'category' => 'devops',   'icon_class' => 'devicon-bash-plain',          'order_index' =>  6],
            // ─── Other ────────────────────────────────────────────────────────
            ['name' => 'Figma',              'level' => 62, 'category' => 'other',    'icon_class' => 'devicon-figma-plain',         'order_index' =>  1],
            ['name' => 'Jira / Trello',      'level' => 80, 'category' => 'other',    'icon_class' => 'devicon-jira-plain',          'order_index' =>  2],
            ['name' => 'Agile / Scrum',      'level' => 78, 'category' => 'other',    'icon_class' => null,                          'order_index' =>  3],
            ['name' => 'OpenAPI / Swagger',  'level' => 84, 'category' => 'other',    'icon_class' => null,                          'order_index' =>  4],
        ];

        $this->table('skills')->insert($skills)->saveData();
        $this->output->writeln('  <info>SkillSeeder</info>: ' . count($skills) . ' skills inserted');
    }
}
