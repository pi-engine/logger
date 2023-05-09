<?php

namespace Logger\Validator;

use Laminas\Validator\AbstractValidator;
use function array_merge;
use function in_array;

class TypeValidator extends AbstractValidator
{
    /** @var string */
    const INVALID = 'typeInvalid';

    /** @var array */
    protected array $messageTemplates = [];

    /** @var array */
    protected array $typeList
        = [
            'public', 'error','login', 'logout', 'register', 'mobile_request', 'email_request', 'mobile_verify', 'email_verify', 'login_failed','register_failed','mobile_request_failed','email_request_failed','mobile_verify_failed','email_verify_failed'
        ];

    /** @var array */
    protected $options
        = [];

    /**
     * {@inheritDoc}
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->messageTemplates = [
            self::INVALID => 'Invalid log type!',
        ];

        parent::__construct($options);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        $this->setValue($value);

        if (!in_array($value, $this->typeList)) {
            $this->error(static::INVALID);
            return false;
        }

        return true;
    }
}