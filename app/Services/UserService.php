<?php

namespace App\Services;

use App\Interfaces\Services\IUserService;

class UserService implements IUserService
{
    public function getVisitorTokenHeaderName(): string
    {
        return "Visitor-Token";
    }

    public function getVisitorToken(): string|null
    {
        return request()->header($this->getVisitorTokenHeaderName());
    }

    public function createVisitorToken(): string
    {
        $uuid = (string) \Str::uuid();
        return $uuid;
    }
}