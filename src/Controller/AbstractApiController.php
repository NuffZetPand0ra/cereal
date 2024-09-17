<?php

namespace App\Controller;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

abstract class AbstractApiController extends AbstractController
{
    private ?Serializer $_serializer = null;

    public function jsonSerialize(mixed $value): mixed
    {
        return $this->getSerializer()->serialize($value, 'json');
    }

    public function jsonDeserialize(string $value, string $type): mixed
    {
        return $this->getSerializer()->deserialize($value, $type, 'json');
    }

    private function getSerializer(): Serializer
    {
        if(!$this->_serializer){
            // $default_context = [
            //     AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
            //         return $object->getId();
            //     }
            // ];
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            // $normalizers = [new ObjectNormalizer(defaultContext: $default_context)];
            $this->_serializer = new Serializer($normalizers, $encoders);
        }
        return $this->_serializer;
    }


}