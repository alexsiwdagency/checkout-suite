<?php
namespace IWD\CheckoutConnector\Model;

use Magento\Framework\FlagManager;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class CacheCleanerFlag
 *
 * @package IWD\CheckoutConnector\Model
 */
class CacheCleanerFlag
{
    const FLAG = 'iwd_checkout_clean_cache';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * CacheCleanerFlag constructor.
     *
     * @param FlagManager $flagManager
     * @param Request $request
     */
    public function __construct(
        FlagManager $flagManager,
        Request $request
    ) {
        $this->flagManager = $flagManager;
        $this->request = $request;
    }

    public function addFlag()
    {
        $this->flagManager->saveFlag(self::FLAG, 1);
    }
}
