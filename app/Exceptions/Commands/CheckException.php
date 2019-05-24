<?php

namespace App\Exceptions\Commands;

/**
 * Class CheckException
 * @package App\Exceptions\Commands
 */
class CheckException extends CommandException
{
    private $attributes;

    public function __construct(array $attributes, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->attributes = is_array($attributes) ? $attributes : [$attributes];

        $message = $message ?: 'field & value uncheck: ' . json_encode($attributes);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
