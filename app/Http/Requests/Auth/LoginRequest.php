<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // R8.1 y R8.2
            'usuario' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'password' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // R9.4: Verificación del usuario y contraseña
        if (! Auth::attempt(
            ['usuario' => $this->usuario, 'password' => $this->password],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'usuario' => 'Usuario o contraseña incorrectos',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'usuario' => 'Demasiados intentos. Intente de nuevo en '.$seconds.' segundos.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::lower($this->input('usuario')).'|'.$this->ip();
    }
}
