<?php

namespace Logger\Service;

use IntlDateFormatter;

class UtilityService implements ServiceInterface
{
    /* @var array */
    protected array $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
    public function inventoryLogListCanonize($objectList): array
    {
        $list = [];
        foreach ($objectList as $object) {
            $list[] = $this->inventoryLogCanonize($object);
        }
        return array_values($list);
    }

    public function inventoryLogCanonize($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id' => (int)$object->getId(),
                'timestamp' => $object->getTimestamp(),
                'priority' => $object->getPriority(),
                'priority_name' => $object->getPriorityName(),
                'message' => $object->getMessage(),
                'extra_data' => $object->getExtraData(),
                'extra_time_create' => $object->getExtraTimeCreate(),
                'extra_user_id' => $object->getExtraUserId(),
            ];
        } else {
            $object = [
                'id' => (int)$object['id'],
                'timestamp' => $object['timestamp'],
                'priority' => $object['priority'],
                'priority_name' => $object['priorityName'],
                'message' => $object['message'],
                'extra_data' => $object['extra_data'],
                'extra_time_create' => $object['extra_time_create'],
                'extra_user_id' => $object['extra_user_id'],
            ];
        }
        $object['extra_data'] = json_decode($object['extra_data'], true);
        $extra = $object['extra_data'];
        $attributes = $extra['attributes'];
        $route = $extra['route'];
        $serverParams = $extra['serverParams'];
        $object['target'] = $extra['target'];
        $object['section'] = $route['section'];
        $object['module'] = $route['module'];
        $object['package'] = $route['package'];
        $object['handler'] = $route['handler'];
        $object['method'] = $extra['method'];
        $object['user_id'] = $extra['user_id'];
        $object['name'] = $attributes['account']['name'];
        $object['email'] = $attributes['account']['email'];
        $object['identity'] = $attributes['account']['identity'];
        $object['mobile'] = $attributes['account']['mobile'];
        $object['roles'] = $attributes['roles'];
        $object['ip'] = $serverParams['REMOTE_ADDR'];
        unset($object['extra_data']);
        $object['time_create_view'] = $this->date($object['extra_time_create']);
        $object['roles_view'] = [];


        return $object;
    }

    public function paramsFraming($params): array
    {
        $limit = (int)($params['limit'] ?? 25);
        $page = (int)($params['page'] ?? 1);
        $order = $params['order'] ?? ['timestamp DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'page' => $page,
        ];

        $paramNames = [
            "ip",
            "order",
            "identity",
            "role",
            "target",
            "name",
            "email",
            "module",
            "section",
            "identity",
            "data_from",
            "data_to",
            "priority_name",
            "method",
            "user_id",
        ];

        $nonEmptyParams = [];

        foreach ($paramNames as $paramName) {
            if (isset($params[$paramName]) && $params[$paramName] !== '') {
                $nonEmptyParams[$paramName] = $params[$paramName];
            }
        }

        if (isset($nonEmptyParams['user_id'])&&!empty((int)$nonEmptyParams['user_id'])) {
            $nonEmptyParams['user_id'] = explode(',', $nonEmptyParams['user_id']);
        }

        if (isset($nonEmptyParams['data_from']) && !empty($nonEmptyParams['data_from'])) {
            $nonEmptyParams['data_from'] = strtotime(
                ($params['data_from']) != null
                    ? sprintf('%s 00:00:00', $params['data_from'])
                    : sprintf('%s 00:00:00', date('Y-m-d', strtotime('-1 month')))
            );
        }

        if (isset($nonEmptyParams['data_to']) && !empty($nonEmptyParams['data_to'])) {
            $nonEmptyParams['data_to'] = strtotime(
                ($nonEmptyParams['data_to']) != null
                    ? sprintf('%s 00:00:00', $params['data_to'])
                    : sprintf('%s 23:59:59', date('Y-m-d'))
            );
        }
        return array_merge($listParams, $nonEmptyParams);
    }

    public function canonizeUserLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id' => (int)$object->getId(),
                'user_id' => $object->getUserId(),
                'operator_id' => $object->getOperatorId(),
                'time_create' => $object->getTimeCreate(),
                'state' => $object->getState(),
                'information' => $object->getInformation(),
                'user_identity' => $object->getUserIdentity(),
                'user_name' => $object->getUserName(),
                'user_email' => $object->getUserEmail(),
                'user_mobile' => $object->getUserMobile(),
            ];
        } else {
            $object = [
                'id' => (int)$object['id'],
                'user_id' => $object['user_id'],
                'operator_id' => $object['operator_id'],
                'time_create' => $object['time_create'],
                'state' => $object['state'],
                'information' => $object['information'],
                'user_identity' => $object['user_identity'],
                'user_name' => $object['user_name'],
                'user_email' => $object['user_email'],
                'user_mobile' => $object['user_mobile'],
            ];
        }

        $information = json_decode($object['information'], true);
        $information['params']['method'] = $information['params']['serverParams']['REQUEST_METHOD'];
        $information['params']['ip'] = $information['params']['serverParams']['REMOTE_ADDR'];
        unset($information['params']['serverParams']);
        $object['information'] = $information;
        $object['time_create_view'] = $this->date($object['time_create']);
        $object['roles_view'] = [];

        return $object;
    }


    public function date(string $date = '', array $params = []): string
    {
        $date = empty($date) ? time() : $date;

        if (!class_exists('IntlDateFormatter')) {
            return date('Y-m-d H:i:s', $date);
        }

        // Set params
        $local    = $params['local'] ?? $this->config['date_local'];
        $datetype = $params['datetype'] ?? $this->config['date_type'];
        $timetype = $params['timetype'] ?? $this->config['time_type'];
        $timezone = $params['timezone'] ?? $this->config['timezone'];
        $calendar = $params['calendar'] ?? $this->config['date_calendar'];
        $pattern  = $params['pattern'] ?? $this->config['date_pattern'];

        $formatter = new IntlDateFormatter($local, $datetype, $timetype, $timezone, $calendar, $pattern);
        return $formatter->format($date);
    }


}