<?php

namespace Logger\Model;

class Logger
{
    private mixed $id;
    private int $user_id;
    private int $item_id;
    private string $action;
    private string $event;
    private string $type;
    private string $date;
    private string $information;
    private int $time_create;
    private int $time_update;
    private int $time_delete;

    /**
     * @param mixed $id
     * @param int $user_id
     * @param int $item_id
     * @param string $action
     * @param string $event
     * @param string $type
     * @param string $date
     * @param int $time_create
     * @param int $time_update
     * @param int $time_delete
     */
    public function __construct(mixed $id, int $user_id, int $item_id, string $action, string $event, string $type, string $date, string $information, int $time_create, int $time_update, int $time_delete)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->item_id = $item_id;
        $this->action = $action;
        $this->event = $event;
        $this->type = $type;
        $this->information = $information;
        $this->date = $date;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->time_delete = $time_delete;
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
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->item_id;
    }

    /**
     * @param int $item_id
     */
    public function setItemId(int $item_id): void
    {
        $this->item_id = $item_id;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    /**
     * @param int $time_create
     */
    public function setTimeCreate(int $time_create): void
    {
        $this->time_create = $time_create;
    }

    /**
     * @return int
     */
    public function getTimeUpdate(): int
    {
        return $this->time_update;
    }

    /**
     * @param int $time_update
     */
    public function setTimeUpdate(int $time_update): void
    {
        $this->time_update = $time_update;
    }

    /**
     * @return int
     */
    public function getTimeDelete(): int
    {
        return $this->time_delete;
    }

    /**
     * @param int $time_delete
     */
    public function setTimeDelete(int $time_delete): void
    {
        $this->time_delete = $time_delete;
    }


    /**
     * @return string
     */
    public function getInformation(): string
    {
        return $this->information;
    }

    /**
     * @param string $information
     */
    public function setInformation(string $information): void
    {
        $this->information = $information;
    }

}
