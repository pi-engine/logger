<?php

namespace Logger\Model;

class User
{
    private mixed  $id;
    private int    $user_id;
    private int    $time_create;
    private string $state;
    private string $information;

    public function __construct(
        $user_id,
        $time_create,
        $state,
        $information,
        $id = null
    ) {
        $this->user_id     = $user_id;
        $this->time_create = $time_create;
        $this->state       = $state;
        $this->information = $information;
        $this->id          = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getInformation(): string
    {
        return $this->information;
    }
}