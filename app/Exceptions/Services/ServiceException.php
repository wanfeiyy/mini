<?php

namespace App\Exceptions\Services;

/**
 * Service 层异常.
 *
 * <p><code>Service</code>异常，这是一个运行时异常。用户无需显示捕获此异常，但是，为了更好的用户体验，建议在全局处理该异常。</p>
 *
 * @package Medlinker\Exceptions
 * @author luoyu
 */
class ServiceException extends \RuntimeException
{
    /**
     * ServiceException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous PHP7.0 以下所有异常的基类为 \Exception, 从 7.0 开始异常基类为 Throwable
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
