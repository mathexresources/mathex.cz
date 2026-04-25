<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\Model\Database\UserRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class Authenticator implements \Nette\Security\Authenticator
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Passwords $passwords,
    ) {
    }

    public function authenticate(string $user, string $password): IIdentity
    {
        $row = $this->users->findByEmail($user);

        if (!$row) {
            usleep(300_000); // slow down enumeration attempts
            throw new AuthenticationException('Nesprávný e-mail nebo heslo.', self::INVALID_CREDENTIAL);
        }

        if (!$row->is_active) {
            throw new AuthenticationException('Tento účet byl deaktivován.', self::NOT_APPROVED);
        }

        // Check brute-force lockout
        if ($row->locked_until !== null) {
            $lockedUntil = new \DateTimeImmutable((string) $row->locked_until);
            if (new \DateTimeImmutable() < $lockedUntil) {
                $minutes = (int) ceil(($lockedUntil->getTimestamp() - time()) / 60);
                throw new AuthenticationException(
                    "Příliš mnoho neúspěšných pokusů. Účet je zablokován na {$minutes} minut.",
                    self::NOT_APPROVED,
                );
            }
        }

        if (!$this->passwords->verify($password, (string) $row->password_hash)) {
            $this->users->recordFailedLogin((int) $row->id);
            throw new AuthenticationException('Nesprávný e-mail nebo heslo.', self::INVALID_CREDENTIAL);
        }

        $this->users->resetFailedLogins((int) $row->id);

        if ($this->passwords->needsRehash((string) $row->password_hash)) {
            $this->users->updatePassword((int) $row->id, $this->passwords->hash($password));
        }

        return new SimpleIdentity((int) $row->id, (string) $row->role, [
            'name'  => $row->name,
            'email' => $row->email,
        ]);
    }
}
