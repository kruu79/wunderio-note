<?php

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * Normalizes Exception to Array which can be processed by JsonResponse.
 *
 * Exception with 5xx status code returns exception message in 'message' property.
 * 'status' property is set to 'error'.
 *
 * Exception with 4xx status code decodes exception message which should be formatted
 * as JSON string in 'data' property.
 * 'status' property is set to 'fail'.
 *
 * @package App\Serializer
 */
class ExceptionNormalizer implements NormalizerInterface
{
    public function normalize($exception, string $format = null, array $context = [])
    {
        if ($this->isServerError($exception->getStatusCode())) {
            return [
                'message' => $exception->getMessage(),
                'status'=> 'error',
            ];
        }
        else {
            return [
                'data' => json_decode($exception->getMessage()),
                'status'=> 'fail',
            ];
        }
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof FlattenException;
    }


    private function isServerError($statusCode) : bool
    {
        return $statusCode >= 500;
    }
}