<?php

namespace App\Interfaces\Services;

interface IUserService
{
    public function getVisitorTokenHeaderName(): string;
    public function getVisitorToken(): string|null;
    public function createVisitorToken(): string;
}