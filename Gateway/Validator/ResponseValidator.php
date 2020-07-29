<?php

namespace IWD\CheckoutConnector\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;

class ResponseValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        if (isset($response['resultCode']) && $response['resultCode'] == 1) {
            return $this->createResult(true);
        }
        $errorMsg = $this->getErrorMessage($response);
        return $this->createResult(false, [$errorMsg]);
    }

    /**
     * @param $response
     * @return \Magento\Framework\Phrase|string
     */
    private function getErrorMessage($response)
    {
        return (isset($response['errorMsg']) && !empty($response['errorMsg']))
            ? 'IWDCheckoutConnector: ' . $response['errorMsg']
            : __('IWDCheckoutConnector: Something was wrong');
    }
}
