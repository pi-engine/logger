<?php

namespace Logger\Model;

class User
{
    private mixed  $id;
    private int    $user_id;
    private int    $time_create;
    private string $state;
    private string $information;
    private mixed $user_identity;
    private mixed $user_name;
    private mixed $user_email;
    private mixed $user_mobile;

    public function __construct(
        $user_id,
        $time_create,
        $state,
        $information,
        $user_identity = null,
        $user_name = null,
        $user_email = null,
        $user_mobile = null,
        $id = null
    ) {
        $this->user_id     = $user_id;
        $this->time_create = $time_create;
        $this->state       = $state;
        $this->information = $information;
        $this->user_identity       = $user_identity;
        $this->user_name           = $user_name;
        $this->user_email          = $user_email;
        $this->user_mobile         = $user_mobile;
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

    public function getUserIdentity(): ?string
    {
        return $this->user_identity;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function getUserEmail(): ?string
    {
        return $this->user_email;
    }

    public function getUserMobile(): ?string
    {
        return $this->user_mobile;
    }
}