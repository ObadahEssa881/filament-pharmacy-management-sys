<?php

namespace App\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\UserProvider;

class NestJsAuthGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->request->session()->get('nestjs_token');

        if ($token) {
            $payload = $this->request->session()->get('user_payload');
            $this->user = new NestJsUser($payload);
            return $this->user;
        }

        return null;
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        $this->setUser($user);
        return true;
    }
}
