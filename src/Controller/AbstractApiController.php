<?php

namespace App\Controller;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            $encoders = [new JsonEncoder(), new XmlEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $this->_serializer = new Serializer($normalizers, $encoders);
        }
        return $this->_serializer;
    }


}