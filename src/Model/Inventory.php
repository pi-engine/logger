<?php

namespace Logger\Model;

class Inventory
{
    private mixed  $id;
    private string $timestamp;
    private int    $priority;
    private string $priorityName;
    private string $message;
    private string $extra_user_id;
    private string $extra_time_create;
    private string $extra_company_id;
    private string $extra_data;

    /**
     * @param mixed  $id
     * @param string $timestamp
     * @param int    $priority
     * @param string $priorityName
     * @param string $message
     * @param string $extra_user_id
     * @param string $extra_time_create
     * @param string $extra_company_id
     * @param string $extra_data
     */
    public function __construct(
        mixed $id,
        string $timestamp,
        int $priority,
        string $priorityName,
        string $message,
        string $extra_user_id,
        string $extra_time_create,
        string $extra_company_id,
        string $extra_data
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
     * @return string
     */
    public function getExtraUserId(): string
    {
        return $this->extra_user_id;
    }

    /**
     * @param string $extra_user_id
     */
    public function setExtraUserId(string $extra_user_id): void
    {
        $this->extra_user_id = $extra_user_id;
    }

    /**
     * @return string
     */
    public function getExtraTimeCreate(): string
    {
        return $this->extra_time_create;
    }

    /**
     * @param string $extra_time_create
     */
    public function setExtraTimeCreate(string $extra_time_create): void
    {
        $this->extra_time_create = $extra_time_create;
    }

    /**
     * @return string
     */
    public function getExtraCompanyId(): string
    {
        return $this->extra_company_id;
    }

    /**
     * @param string $extra_company_id
     */
    public function setExtraCompanyId(string $extra_company_id): void
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