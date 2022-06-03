<?php

namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 *
 *
 * Class ExceptionNormalizer
 * @package App\Serializer
 */
class ExceptionNormalizer implements NormalizerInterface
{
    public function normalize($exception, string $format = null, array $context = [])
    {
        return [
            'data' => null,
            'status'=> 'fail',
            'message' => $exception->getMessage(),
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof FlattenException;
    }
}