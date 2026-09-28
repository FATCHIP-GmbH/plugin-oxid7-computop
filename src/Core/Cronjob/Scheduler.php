<?php

namespace Fatchip\ComputopPayments\Core\Cronjob;

use OxidEsales\Eshop\Core\Registry;

class Scheduler
{
    protected $aCronjobs = [
        FinalizeOrder::class,
    ];


    /**
     * Returns list of all available cronjobs
     *
     * @return array
     */
    protected function getCronjobs()
    {
        return $this->aCronjobs;
    }

    /**
     * Check if cronjob is due again
     *
     * @param  Base $oCronjob
     * @return bool
     */
    protected function isCronjobDue(Base $oCronjob)
    {
        $iGracePeriod = 5; // Grace period timer to prevent cronjob not starting when crontab timer and minute invterval are exactly the same
        if (empty($oCronjob->getLastRunDateTime()) || (strtotime($oCronjob->getLastRunDateTime()) - $iGracePeriod) <= (time() - ($oCronjob->getMinuteInterval() * 60))) {
            return true;
        }
        return false;
    }

    /**
     * Starts all available cronjobs
     *
     * @param  int|false $iShopId
     * @return void
     */
    public function start($iShopId = false)
    {
        Base::outputInfo("START CRONJOB EXECUTION");

        $oConfig = Registry::getConfig();
        if ($iShopId !== false) {
            $oConfig->setShopId($iShopId);
            Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);
        } else {
            $iShopId = $oConfig->getShopId();
        }

        foreach ($this->getCronjobs() as $sCronjobClass) {
            $oCronjob = oxNew($sCronjobClass, $iShopId);
            if ($oCronjob->isCronjobActivated() && $this->isCronjobDue($oCronjob)) {
                $oCronjob->startCronjob();
            }
        }

        Base::outputInfo("FINISHED CRONJOB EXECUTION");
    }
}
