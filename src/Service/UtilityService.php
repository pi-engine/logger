<?php

namespace Logger\Service;

class UtilityService implements ServiceInterface
{
    public function __construct()
    {
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
                'id'            => (int)$object->getId(),
                'timestamp'     => $object->getTimestamp(),
                'priority'      => $object->getPriority(),
                'priority_name' => $object->getPriorityName(),
                'message'       => $object->getMessage(),
                'extra_data'    => $object->getExtraData(),
            ];
        } else {
            $object = [
                'id'            => (int)$object['id'],
                'timestamp'     => $object['timestamp'],
                'priority'      => $object['priority'],
                'priority_name' => $object['priorityName'],
                'message'       => $object['message'],
                'extra_data'    => $object['extra_data'],
            ];
        }
        $object['extra_data'] = json_decode($object['extra_data'], true);
        $extra                = $object['extra_data'];
        $attributes           = $extra['attributes'];
        $route                = $extra['route'];
        $object['target']     = $extra['target'];
        $object['section']    = $route['section'];
        $object['module']     = $route['module'];
        $object['package']    = $route['package'];
        $object['handler']    = $route['handler'];
        $object['method']     = $extra['method'];
        $object['user_id']    = $extra['user_id'];
        $object['name']       = $attributes['account']['name'];
        $object['email']      = $attributes['account']['email'];
        $object['identity']   = $attributes['account']['identity'];
        $object['mobile']     = $attributes['account']['mobile'];
        $object['roles']      = $attributes['roles'];

        unset($object['extra_data']);

        return $object;
    }

    public function paramsFraming($params): array
    {
        $limit  = $params['limit'] ?? 50;
        $page   = $params['page'] ?? 1;
        $order  = $params['order'] ?? ['timestamp DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        $paramNames = [
            "name",
            "email",
            "module",
            "section",
            "identity",
            "from_date",
            "to_date",
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

        if (isset($nonEmptyParams['user_id'])) {
            $nonEmptyParams['user_id'] = explode(',', $nonEmptyParams['user_id']);
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
                'id'            => (int)$object->getId(),
                'user_id'       => $object->getUserId(),
                'time_create'   => $object->getTimeCreate(),
                'state'         => $object->getState(),
                'information'   => $object->getInformation(),
                'user_identity' => $object->getUserIdentity(),
                'user_name'     => $object->getUserName(),
                'user_email'    => $object->getUserEmail(),
                'user_mobile'   => $object->getUserMobile(),
            ];
        } else {
            $object = [
                'id'            => (int)$object['id'],
                'user_id'       => $object['user_id'],
                'time_create'   => $object['time_create'],
                'state'         => $object['state'],
                'information'   => $object['information'],
                'user_identity' => $object['user_identity'],
                'user_name'     => $object['user_name'],
                'user_email'    => $object['user_email'],
                'user_mobile'   => $object['user_mobile'],
            ];
        }

        $object['information'] = json_decode($object['information'], true);
        //$object['time_create'] = $this->utilityService->date($object['time_create']);

        return $object;
    }

}