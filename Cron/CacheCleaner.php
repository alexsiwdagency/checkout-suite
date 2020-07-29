<?php

namespace IWD\CheckoutConnector\Cron;

use Magento\Framework\FlagManager;
use IWD\CheckoutConnector\Model\CacheCleanerFlag;
use IWD\CheckoutConnector\Helper\CacheCleaner as CacheCleanerHelper;

/**
 * Class CacheCleaner
 *
 * @package IWD\CheckoutConnector\Cron
 */
class CacheCleaner
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var CacheCleanerHelper
     */
    private $cacheCleanerHelper;

    /**
     * CacheCleaner constructor.
     *
     * @param FlagManager $flagManager
     * @param CacheCleanerHelper $cacheCleanerHelper
     */
    public function __construct(
        FlagManager $flagManager,
        CacheCleanerHelper $cacheCleanerHelper
    ) {
        $this->flagManager = $flagManager;
        $this->cacheCleanerHelper = $cacheCleanerHelper;
    }

    /**
     * execute
     */
    public function execute()
    {
        if ($this->flagManager->getFlagData(CacheCleanerFlag::FLAG)) {
            $this->cacheCleanerHelper->flushCache();

            $this->flagManager->deleteFlag(CacheCleanerFlag::FLAG);
        }
    }
}
