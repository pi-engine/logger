<?php

namespace Logger\Model;

class Inventory
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

    /**
     * @param string     $timestamp
     * @param int        $priority
     * @param string     $priorityName
     * @param string     $message
     * @param int     $extra_user_id
     * @param int     $extra_company_id
     * @param int     $extra_time_create
     * @param string     $extra_data
     * @param mixed|null $id
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
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getPriorityName(): string
    {
        return $this->priorityName;
    }

    /**
     * @param string $priorityName
     */
    public function setPriorityName(string $priorityName): void
    {
        $this->priorityName = $priorityName;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getExtraUserId(): int
    {
        return $this->extra_user_id;
    }

    /**
     * @param int $extra_user_id
     */
    public function setExtraUserId(int $extra_user_id): void
    {
        $this->extra_user_id = $extra_user_id;
    }

    /**
     * @return int
     */
    public function getExtraTimeCreate(): int
    {
        return $this->extra_time_create;
    }

    /**
     * @param int $extra_time_create
     */
    public function setExtraTimeCreate(int $extra_time_create): void
    {
        $this->extra_time_create = $extra_time_create;
    }

    /**
     * @return int
     */
    public function getExtraCompanyId(): int
    {
        return $this->extra_company_id;
    }

    /**
     * @param int $extra_company_id
     */
    public function setExtraCompanyId(int $extra_company_id): void
    {
        $this->extra_company_id = $extra_company_id;
    }

    /**
     * @return string
     */
    public function getExtraData(): string
    {
        return $this->extra_data;
    }

    /**
     * @param string $extra_data
     */
    public function setExtraData(string $extra_data): void
    {
        $this->extra_data = $extra_data;
    }
}