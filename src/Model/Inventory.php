<?php

namespace Logger\Model;

class Inventory
{
    private mixed  $id;
    private string $timestamp;
    private int    $priority;
    private string $priorityName;
    private string $message;
    private string $extra_data;

    public function __construct(
        $timestamp,
        $priority,
        $priorityName,
        $message,
        $extra_data,
        $id = null
    ) {
        $this->timestamp    = $timestamp;
        $this->priority     = $priority;
        $this->priorityName = $priorityName;
        $this->message      = $message;
        $this->extra_data        = $extra_data;
        $this->id           = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getPriorityName(): string
    {
        return $this->priorityName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getExtraData(): string
    {
        return $this->extra_data;
    }
}