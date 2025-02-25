<?php

/**
 * The Computop Oxid Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Computop Oxid Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Computop Oxid Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 8.1, 8.2
 *
 * @category   Payment
 * @package    fatchip-gmbh/computop_payments
 * @subpackage Admin_FatchipComputopConfig
 * @author     FATCHIP GmbH <support@fatchip.de>
 * @copyright  2024 Computop UpdateIdealIssuers
 * @license    <http://www.gnu.org/licenses/> GNU Lesser General Public License
 * @link       https://www.computop.com
 */

namespace Fatchip\ComputopPayments\Controller;

use Fatchip\ComputopPayments\Core\Config;
use Fatchip\ComputopPayments\Core\Constants;
use Fatchip\ComputopPayments\Model\IdealIssuers;
use Fatchip\ComputopPayments\Service\ModuleSettings;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Core\Model\ListModel;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use Symfony\Component\String\UnicodeString;

/**
 * @mixin PaymentController
 */
class FatchipComputopPayment extends FatchipComputopPayment_parent
{
    protected $fatchipComputopConfig;

    protected array $frontendHiddenPayments = [
        'fatchip_computop_paypal_express'
    ];

    // -----------------> START OXID CORE MODULE EXTENSIONS <-----------------

    /**
     * Executes parent::render(), checks if this connection secure
     * (if not - redirects to secure payment page), loads user object
     * (if user object loading was not successfull - redirects to start
     * page), loads user delivery/shipping information. According
     * to configuration in admin, user profile data loads delivery sets,
     * and possible payment methods. Returns name of template to render
     * payment::_sThisTemplate.
     *
     * @return  string  current template file name
     */
    public function render()
    {
        Registry::getSession()->handlePaymentSession();
        return parent::render();
    }

    /**
     * Template variable getter. Returns paymentlist
     *
     * @return object
     */
    public function getPaymentList()
    {
        /** @var PaymentList $oPaymentList */
        $oPaymentList = parent::getPaymentList();

        /** @var Payment $oPayment */
        foreach ($oPaymentList as $oPayment) {
            if (in_array($oPayment->getId(), $this->frontendHiddenPayments)) {
                unset($oPaymentList[$oPayment->getId()]);
            }
        }

        return $oPaymentList;
    }

    // -----------------> END OXID CORE MODULE EXTENSIONS <-----------------

    // -----------------> START CUSTOM MODULE FUNCTIONS <-----------------
    // @TODO: They ALL need a module function name prefix to not cross paths with other modules

    public function getFatchipComputopConfig()
    {
        $config = new Config();
        $this->fatchipComputopConfig = $config->toArray();
        return $this->fatchipComputopConfig;
    }

    /**
     * Returns an array with range of given numbers as pad formatted string
     *
     * @param int $iFrom
     * @param int $iTo
     * @param int $iPositions
     * @return array
     */
    protected function getNumericRange($iFrom, $iTo, $iPositions)
    {
        for ($iCurrentNumber = $iFrom; $iCurrentNumber <= $iTo; $iCurrentNumber++) {
            $aRange[] = str_pad($iCurrentNumber, $iPositions, '0', STR_PAD_LEFT);
        }

        return $aRange;
    }

    /**
     * Returns an array of available months
     *
     * @return array
     */
    public function getBirthdateMonths()
    {
        return $this->getNumericRange(1, 12, 2);
    }

    /**
     * Returns an array of available days
     *
     * @return array
     */
    public function getBirthdateDays()
    {
        return $this->getNumericRange(1, 31, 2);
    }

    /**
     * Template getter which delivers certain parts of birthdate
     *
     * @param string $sPart (year,month,day)
     * @return string
     */
    public function getBirthdayField($sPart)
    {
        $oUser = $this->getUser();
        $sBirthdate = $oUser->getFieldData('oxbirthdate');
        $aBirthdateParts = explode('-', $sBirthdate);
        $aMap = ['year' => 0, 'month' => 1, 'day' => 2];

        $sReturn = '';
        if (isset($aBirthdateParts[$aMap[$sPart]])) {
            $sReturn = $aBirthdateParts[$aMap[$sPart]];
        }

        return $sReturn;
    }


    /**
     * Template getter which checks if requesting birthdate is needed
     */
    public function showBirthdate(): bool
    {
        $oUser = $this->getUser();
        return $oUser->getFieldData('oxbirthdate') === '0000-00-00';
    }

    /**
     * Template getter for returning an array with last hundred years
     */
    public function getYearRange(): array
    {
        $iCurrentYear = (int)date('Y');
        $iHundredYearsAgo = $iCurrentYear - 100;

        $aRange = $this->getNumericRange($iHundredYearsAgo, $iCurrentYear, 4);
        $aReverse = array_reverse($aRange);
        foreach ($aReverse as $sYear) {
            $aReturn[] = $sYear;
        }

        return $aReturn;
    }

    /**
     * List for PaymentView
     * @return array|false
     */
    public function getIdealIssuers()
    {
        $oIdealIssuers = oxNew(IdealIssuers::class);
        $sql = $oIdealIssuers->buildSelectString();
        $oList = oxNew(ListModel::class);
        $oList->init(IdealIssuers::class);
        $oList->selectString($sql);
        $oIssuerList = $oList->getArray();
        if ($oIssuerList) {
            return $oIssuerList;
        }
        return false;
    }

    /**
     * @return UnicodeString
     */
    public function idealDirektOderUeberSofort()
    {
        /** @var ModuleSettings $moduleSettings */
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        return $moduleSettingService->getString('idealDirektOderUeberSofort', Constants::MODULE_ID);
    }
}
