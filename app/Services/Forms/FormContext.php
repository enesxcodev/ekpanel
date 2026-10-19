<?php

namespace App\Services\Forms;

use App\Models\Forms\SiteForm;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use JsonException;

class FormContext
{
    /** @var array<string, Closure(int, SiteForm): ?array> */
    private array $resolvers = [];

    public function register(string $type, Closure $resolver): void
    {
        $this->resolvers[$type] = $resolver;
    }

    public function issue(string $type, int $id, SiteForm $form): string
    {
        abort_unless(isset($this->resolvers[$type]) && ($this->resolvers[$type])($id, $form) !== null, 404);

        return Crypt::encryptString(json_encode(['type' => $type, 'id' => $id, 'form' => $form->id, 'expires' => now()->addHours(2)->timestamp], JSON_THROW_ON_ERROR));
    }

    /** @return array{type: string, id: int, title: string, slug: string}|null */
    public function resolve(mixed $token, SiteForm $form): ?array
    {
        if ($token === null) {
            return null;
        }
        try {
            if (! is_string($token) || $token === '' || strlen($token) > 4096) {
                throw new \UnexpectedValueException;
            }
            $data = json_decode(Crypt::decryptString($token), true, 16, JSON_THROW_ON_ERROR);
            if (! is_array($data) || ! is_string($data['type'] ?? null) || ! is_int($data['id'] ?? null)
                || ($data['form'] ?? null) !== $form->id || ! is_int($data['expires'] ?? null)
                || $data['expires'] < now()->timestamp || ! isset($this->resolvers[$data['type']])) {
                throw new \UnexpectedValueException;
            }
            $context = ($this->resolvers[$data['type']])($data['id'], $form);
            if ($context === null) {
                throw new \UnexpectedValueException;
            }

            return $context;
        } catch (DecryptException|JsonException|\UnexpectedValueException) {
            throw ValidationException::withMessages(['form_context' => 'İlgili içerik veya form bağlantısı artık geçerli değil. Sayfayı yenileyin.']);
        }
    }
}
