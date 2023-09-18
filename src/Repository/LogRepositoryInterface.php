<?php

namespace Logger\Repository;

interface LogRepositoryInterface
{
    public function addUser(array $params = []): void;
}