<?php

namespace Logger\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Formatter\Json;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Db;
use Laminas\Log\Writer\MongoDB;
use Laminas\Log\Writer\Stream;
use Logger\Repository\LogRepositoryInterface;
use MongoDB\Driver\Manager;
use User\Service\UtilityService;

class LoggerService implements ServiceInterface
{
    /** @var LogRepositoryInterface */
    protected LogRepositoryInterface $logRepository;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /* @var array */
    protected array $config;

    /* @var int */
    protected int $priority = Logger::INFO;

    /* @var string */
    protected string $tableLog = 'log_inventory';

    protected array $forbiddenParams
        = [
            'credential',
            'credentialColumn',
            'access_token',
            'refresh_token',
            'token_payload',
            'permission',
        ];

    public function __construct(
        LogRepositoryInterface $logRepository,
        UtilityService $utilityService,
        $config
    ) {
        $this->logRepository  = $logRepository;
        $this->utilityService = $utilityService;
        $this->config         = $config;
    }

    public function setPriority($priority): void
    {
        switch ($priority) {
            case 0:
                $this->priority = Logger::EMERG;
                break;
            case 1:
                $this->priority = Logger::ALERT;
                break;
            case 2:
                $this->priority = Logger::CRIT;
                break;
            case 3:
                $this->priority = Logger::ERR;
                break;
            case 4:
                $this->priority = Logger::WARN;
                break;
            case 5:
                $this->priority = Logger::NOTICE;
                break;
            case 6:
                $this->priority = Logger::INFO;
                break;
            case 7:
                $this->priority = Logger::DEBUG;
                break;
        }
    }

    public function write(string $message, array $params = [], int $priority = null): void
    {
        // Clean up
        $params = $this->cleanupForbiddenKeys($params);

        // Set priority
        if (is_numeric($priority)) {
            $this->setPriority($priority);
        }

        // Save log
        $storage = $this->config['storage'] ?? 'disable';
        switch ($storage) {
            case 'mysql':
                $this->writeToMysql($message, $params);
                break;

            case 'mongodb':
                $this->writeToMongo($message, $params);
                break;

            case 'file':
                $this->writeToFile($message, $params);
                break;

            case '':
            case 'disable':
            default:
                break;
        }
    }

    public function writeToMysql(string $message, array $params): void
    {
        // Set data
        $data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

        // Set writer
        $db     = new Adapter($this->config['mysql']);
        $writer = new Db($db, $this->tableLog);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log(Logger::INFO, $message, [
            'time_create' => time(),
            'user_id'     => (int)$params['user_id'],
            'company_id'  => (int)$params['company_id'],
            'data'        => $data,
        ]);

        // Check and cleanup
        if (isset($this->config['cleanup']) && (bool)$this->config['cleanup'] === true) {
            $this->cleanUpMysql();
        }
    }

    public function writeToMongo(string $message, array $params): void
    {
        $manager = new Manager($this->config['mongodb']['uri']);
        $writer  = new MongoDB(
            $manager,
            $this->config['mongodb']['database'],
            $this->config['mongodb']['collection']
        );

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, $params);
    }

    public function writeToFile(string $message, array $params): void
    {
        // Set file path
        $path = sprintf('%s/%s.log', $this->config['file']['path'], date($this->config['file']['date_format']));

        // Set writer
        $formatter = new Json();
        $writer    = new Stream($path);
        $writer->setFormatter($formatter);

        // Save log
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->log($this->priority, $message, $params);
    }

    public function cleanUpMysql(): void
    {
        $limitation = $this->config['limitation'] ?? 10000;
        $this->logRepository->cleanup($limitation);
    }

    // ToDo: Finish it
    public function cleanUpMongo(): void
    {
    }

    // ToDo: Finish it
    public function cleanUpFile(): void
    {
    }

    public function cleanupForbiddenKeys(array $params): array
    {
        foreach ($params as $key => $value) {
            if (in_array($key, $this->forbiddenParams)) {
                unset($params[$key]);
            } elseif (is_array($value)) {
                $params[$key] = $this->cleanupForbiddenKeys($value);
            }
        }

        return $params;
    }

    public function readInventoryLog($params): array
    {
        $list          = [];
        $listParams    = $this->paramsFraming($params);
        $inventoryList = $this->logRepository->readInventoryLog($listParams);
        foreach ($inventoryList as $object) {
            $list[] = $this->canonizeInventoryLog($object);
        }

        // Get count
        $count = $this->logRepository->getInventoryLogCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => array_values($list),
                'paginator' => [
                    'count' => $count,
                    'limit' => (int)$listParams['limit'],
                    'page'  => (int)$listParams['page'],
                ],
                'filters'   => null,
            ],
            'error'  => [],
        ];
    }

    public function addUserLog(string $state, array $params): void
    {
        $params = [
            'user_id'     => (int)($params['account']['id'] ?? 0),
            'operator_id' => (int)(isset($params['operator']) ? !empty($params['operator']) ? $params['operator']['id'] ?? 0 : 0 : 0),
            'time_create' => time(),
            'state'       => $state,
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        $this->logRepository->addUser($params);
    }

    public function getUserLog($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        if (isset($params['state']) && !empty($params['state'])) {
            $listParams['state'] = $params['state'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['method'] = $params['method'];
        }
        if (isset($params['role']) && !empty($params['role'])) {
            $listParams['role'] = $params['role'];
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            if (is_string($params['user_id']) && str_contains($params['user_id'], ',')) {
                $listParams['user_id'] = explode(',', $params['user_id']);
            } else {
                $listParams['user_id'] = $params['user_id'];
            }
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(
                ($params['data_from']) != null
                    ? sprintf('%s 00:00:00', $params['data_from'])
                    : sprintf('%s 00:00:00', date('Y-m-d', strtotime('-1 month')))
            );
        }

        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(
                ($params['data_to']) != null
                    ? sprintf('%s 00:00:00', $params['data_to'])
                    : sprintf('%s 23:59:59', date('Y-m-d'))
            );
        }

        // Get list
        $list   = [];
        $rowSet = $this->logRepository->getUserList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeUserLog($row);
        }

        // Get count
        $count = $this->logRepository->getUserCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function paramsFraming($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['timestamp DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
            'page'   => $page,
        ];

        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }

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

        if (isset($nonEmptyParams['user_id']) && !empty((int)$nonEmptyParams['user_id'])) {
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

    public function canonizeInventoryLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'                => (int)$object->getId(),
                'timestamp'         => $object->getTimestamp(),
                'priority'          => $object->getPriority(),
                'priority_name'     => $object->getPriorityName(),
                'message'           => $object->getMessage(),
                'extra_data'        => $object->getExtraData(),
                'extra_time_create' => $object->getExtraTimeCreate(),
                'extra_user_id'     => $object->getExtraUserId(),
                'extra_company_id'  => $object->getExtraCompanyId(),
            ];
        } else {
            $object = [
                'id'                => (int)$object['id'],
                'timestamp'         => $object['timestamp'],
                'priority'          => $object['priority'],
                'priority_name'     => $object['priorityName'],
                'message'           => $object['message'],
                'extra_data'        => $object['extra_data'],
                'extra_time_create' => $object['extra_time_create'],
                'extra_user_id'     => $object['extra_user_id'],
                'extra_company_id'  => $object['extra_company_id'],
            ];
        }

        // Set information
        $information = !empty($object['extra_data']) ? json_decode($object['extra_data'], true) : [];
        unset($object['extra_data']);

        // Set security report
        $streamSecurity = null;
        if (isset($information['request']['attributes']['security_stream']) && !empty($information['request']['attributes']['security_stream'])) {
            foreach ($information['request']['attributes']['security_stream'] as $securityItem) {
                $streamSecurity[] = [
                    'name'   => $securityItem['name'],
                    'status' => $securityItem['status'],
                ];
            }
        }

        // Set output params
        $object['time_create_view'] = $this->utilityService->date($object['extra_time_create']);
        $object['user_id']          = $information['user_id'] ?? null;
        $object['ip']               = $information['ip'] ?? null;
        $object['title']            = $information['route']['title'] ?? null;
        $object['method']           = $information['request']['method'] ?? null;
        $object['target']           = $information['request']['target'] ?? null;
        $object['section']          = $information['route']['section'] ?? null;
        $object['module']           = $information['route']['module'] ?? null;
        $object['package']          = $information['route']['package'] ?? null;
        $object['handler']          = $information['route']['handler'] ?? null;
        $object['name']             = $information['request']['attributes']['account']['name'] ?? null;
        $object['email']            = $information['request']['attributes']['account']['email'] ?? null;
        $object['identity']         = $information['request']['attributes']['account']['identity'] ?? null;
        $object['mobile']           = $information['request']['attributes']['account']['mobile'] ?? null;
        $object['company_id']       = $information['company_id'] ?? null;
        $object['company_title']    = $information['request']['attributes']['company_authorization']['company']['title'] ?? null;
        $object['package_id']       = $information['request']['attributes']['company_authorization']['package']['id'] ?? null;
        $object['package_title']    = $information['request']['attributes']['company_authorization']['package']['title'] ?? null;
        $object['request_body']     = $information['request']['parsedBody'] ?? null;
        $object['security_stream']  = $streamSecurity;

        return $object;
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
                'operator_id'   => $object->getOperatorId(),
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
                'operator_id'   => $object['operator_id'],
                'time_create'   => $object['time_create'],
                'state'         => $object['state'],
                'information'   => $object['information'],
                'user_identity' => $object['user_identity'],
                'user_name'     => $object['user_name'],
                'user_email'    => $object['user_email'],
                'user_mobile'   => $object['user_mobile'],
            ];
        }

        // Set information
        $object['information'] = json_decode($object['information'], true);

        // Unset not used data
        unset($object['information']['params']['serverParams']);

        // Set data
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        return $object;
    }
}