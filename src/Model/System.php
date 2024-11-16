<?php

namespace Pi\Logger\Model;

class System
{
    private mixed  $id;
    private string $timestamp;
    private int    $priority;
    private string $priorityName;
    private string $message;
    private int    $extra_user_id;
    private int    $extra_company_id;
    private int    $extra_time_create;
    private string $extra_data;
    private mixed  $user_identity;
    private mixed  $user_name;
    private mixed  $user_email;
    private mixed  $user_mobile;

    /**
     * @param string      $timestamp
     * @param int         $priority
     * @param string      $priorityName
     * @param string      $message
     * @param int         $extra_user_id
     * @param int         $extra_company_id
     * @param int         $extra_time_create
     * @param string      $extra_data
     * @param string|null $user_identity
     * @param string|null $user_name
     * @param string|null $user_email
     * @param string|null $user_mobile
     * @param mixed|null  $id
     */
    public function __construct(
        string $timestamp,
        int $priority,
        string $priorityName,
        string $message,
        int $extra_user_id,
        int $extra_company_id,
        int $extra_time_create,
        string $extra_data,
        string $user_identity = null,
        string $user_name = null,
        string $user_email = null,
        string $user_mobile = null,
        mixed $id = null
    ) {
        $this->id                = $id;
        $this->timestamp         = $timestamp;
        $this->priority          = $priority;
        $this->priorityName      = $priorityName;
        $this->message           = $message;
        $this->extra_user_id     = $extra_user_id;
        $this->extra_time_create = $extra_time_create;
        $this->extra_company_id  = $extra_company_id;
        $this->extra_data        = $extra_data;
        $this->user_identity     = $user_identity;
        $this->user_name         = $user_name;
        $this->user_email        = $user_email;
        $this->user_mobile       = $user_mobile;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getPriorityName(): string
    {
        return $this->priorityName;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getExtraUserId(): int
    {
        return $this->extra_user_id;
    }

    /**
     * @return int
     */
    public function getExtraTimeCreate(): int
    {
        return $this->extra_time_create;
    }

    /**
     * @return int
     */
    public function getExtraCompanyId(): int
    {
        return $this->extra_company_id;
    }

    /**
     * @return string
     */
    public function getExtraData(): string
    {
        return $this->extra_data;
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