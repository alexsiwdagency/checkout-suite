<?php

namespace IWD\CheckoutConnector\Gateway\Http\Converter;

use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Framework\Serialize\Serializer\Json;

class JsonConverter implements ConverterInterface
{
    /**
     * @var Json
     */
    private $serializer;

    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $response
     * @return array|bool|float|int|mixed|string|null
     */
    public function convert($response)
    {
        return $this->serializer->unserialize($response);
    }
}
