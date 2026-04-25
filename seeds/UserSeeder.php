<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class UserSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->table('users')->truncate();

        $this->table('users')->insert([
            [
                'email'         => 'admin@mathex.cz',
                'password_hash' => password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost' => 12]),
                'name'          => 'Mathex Admin',
                'role'          => 'admin',
                'is_active'     => 1,
                'created_at'    => '2024-01-01 09:00:00',
                'updated_at'    => '2024-01-01 09:00:00',
            ],
        ])->saveData();

        $this->output->writeln('  <info>UserSeeder</info>: 1 admin user created (admin@mathex.cz / Admin1234!)');
    }
}
