<?php

namespace Modules\Users\Services;

use App\Services\Fields\FieldFiles;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\Users\Models\User;

class UserService
{
    public function __construct(private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?User $user = null): User
    {
        $data = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user === null ? 'required' : 'nullable', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user)],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_image' => ['sometimes', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ])->validate();

        return $this->files->transaction(function (FieldFiles $files) use ($data, $user): User {
            $user = $user === null ? new User : User::query()->lockForUpdate()->findOrFail($user->id);
            $user->fill(collect($data)->except(['profile_image', 'remove_profile_image', 'password'])->all());
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->profile_image = $files->replace($data['profile_image'] ?? null, $user->profile_image, 'users/profiles', (bool) ($data['remove_profile_image'] ?? false));
            $user->save();

            return $user;
        });
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
