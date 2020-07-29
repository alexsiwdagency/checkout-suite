<?php

namespace IWD\CheckoutConnector\Model;

/**
 * Data object converter for REST
 */
class ServiceOutputProcessor extends \Magento\Framework\Webapi\ServiceOutputProcessor
{
    /**
     * @param mixed $data
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return array|bool|float|int|mixed|object|string
     */
    public function process($data, $serviceClassName, $serviceMethodName)
    {
        $dataType = $this->methodsMapProcessor->getMethodReturnType($serviceClassName, $serviceMethodName);

        if ($dataType == 'array_iwd') {
            return $data;
        } else {
            return $this->convertValue($data, $dataType);
        }
    }
}
