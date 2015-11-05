<?php

namespace HyperMedia\Exceptions;


class InvalidResourceAccessException extends \Exception
{

    /**
     * InvalidResourceException constructor.
     * @param string $message
     */
    public function __construct($message = "Invalid resource name has been acceessed")
    {
        parent::__construct($message);
    }
}