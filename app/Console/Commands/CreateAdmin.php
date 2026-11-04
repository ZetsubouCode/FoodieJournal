<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'foodie:admin';

    protected $description = 'Create the first owner through secure interactive prompts';

    public function handle(): int
    {
        if (User::where('is_admin', true)->exists()) {
            $this->error('Admin sudah ada. Gunakan prosedur pemulihan yang terkontrol.');

            return self::FAILURE;
        }
        $data = ['name' => $this->ask('Nama'), 'email' => $this->ask('Email'), 'password' => $this->secret('Kata sandi (minimal 12 karakter)'), 'password_confirmation' => $this->secret('Ulangi kata sandi')];
        $validator = Validator::make($data, ['name' => 'required|string|max:160', 'email' => 'required|email|max:254|unique:user,email', 'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User;
        $user->fill(collect($data)->only(['name', 'email', 'password'])->all());
        $user->is_admin = true;
        $user->save();
        $this->info('Admin dibuat. Masuk melalui /admin/login.');

        return self::SUCCESS;
    }
}
