<?php

namespace Firebase\JWT;

interface JWTExceptionWithPayloadInterface
{
    /**
     * Get the payload that caused this exception.
     */
    public function getPayload(): object;

    /**
     * Get the payload that caused this exception.
     */
    public function setPayload(object $payload): void;
}
