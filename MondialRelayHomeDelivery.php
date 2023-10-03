<?php

namespace MondialRelayHomeDelivery;


use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryAreaFreeshippingQuery;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryFreeshippingQuery;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryInsurance;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryPrice;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryPriceQuery;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryZoneConfiguration;
use PDO;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Translation\Translator;
use Thelia\Exception\TheliaProcessException;
use Thelia\Install\Database;
use Thelia\Model\Area;
use Thelia\Model\AreaDeliveryModule;
use Thelia\Model\AreaQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryArea;
use Thelia\Model\CountryQuery;
use Thelia\Model\Currency;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\OrderPostage;
use Thelia\Model\State;
use Thelia\Module\AbstractDeliveryModuleWithState;
use Thelia\Module\Exception\DeliveryException;

class MondialRelayHomeDelivery extends AbstractDeliveryModuleWithState
{
    /** @var string */
    const DOMAIN_NAME = 'mondialrelayhomedelivery';

    const CODE_ENSEIGNE  = 'code_enseigne';
    const PRIVATE_KEY    = 'private_key';
    const WEBSERVICE_URL = 'webservice_url';
    const GOOGLE_MAPS_API_KEY = 'google_maps_api_key';

    const ALLOW_RELAY_DELIVERY = 'allow_relay_delivery';
    const ALLOW_HOME_DELIVERY  = 'allow_home_delivery';

    const ALLOW_INSURANCE  = 'allow_insurance';

    const TRACKING_MESSAGE_NAME = 'mondial-relay-home-delivery-tracking-message';

    const MONDIAL_RELAY_HOME_DELIVERY_TAX_RULE_ID = 'mondial_relay_home_delivery_tax_rule_id';

    const MAX_WEIGHT_KG = 30;
    const MIN_WEIGHT_KG = 0.1;

    public function postActivation(ConnectionInterface $con = null): void
    {
        try {
            MondialRelayHomeDeliveryPriceQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con);
            $database->insertSql(null, [ __DIR__ . '/Config/TheliaMain.sql' ]);

            // Test Enseigne and private key
            self::setConfigValue(self::CODE_ENSEIGNE, "BDTEST13");
            self::setConfigValue(self::PRIVATE_KEY, "PrivateK");
            self::setConfigValue(self::WEBSERVICE_URL, "https://api.mondialrelay.com/Web_Services.asmx?WSDL");
            self::setConfigValue(self::ALLOW_INSURANCE, true);

            // Create mondial relay shipping zones for relay and home delivery

            $moduleId = self::getModuleId();

            $rateFromEuro = Currency::getDefaultCurrency()->getRate();

            $moduleConfiguration = json_decode(file_get_contents(__DIR__. '/Config/config-data.json'));

            if (false === $moduleConfiguration) {
                throw new TheliaProcessException("Invalid JSON configuration for Mondial Relay module");
            }

            // Create all shipping zones, and associate Mondial relay module with them.
            foreach ($moduleConfiguration->shippingZones as $shippingZone) {
                AreaQuery::create()->filterByName($shippingZone->name)->delete();

                $area = new Area();

                $area
                    ->setName($shippingZone->name)
                    ->save();

                foreach ($shippingZone->countries as $countryIsoCode) {
                    if (null !== $country = CountryQuery::create()->findOneByIsoalpha3($countryIsoCode)) {
                        (new CountryArea())
                            ->setAreaId($area->getId())
                            ->setCountryId($country->getId())
                            ->save();
                    }
                }

                // Define zone attributes
                (new MondialRelayHomeDeliveryZoneConfiguration())
                    ->setAreaId($area->getId())
                    ->setDeliveryTime($shippingZone->delivery_time_in_days)
                    ->save();

                // Attach this zone to our module
                (new AreaDeliveryModule())
                    ->setArea($area)
                    ->setDeliveryModuleId($moduleId)
                    ->save();

                // Create base prices
                foreach ($shippingZone->prices as $price) {
                    (new MondialRelayHomeDeliveryPrice())
                        ->setAreaId($area->getId())
                        ->setMaxWeight($price->up_to)
                        ->setPriceWithTax($price->price_euro * $rateFromEuro)
                        ->save();
                }
            }

            // Insurances
            foreach ($moduleConfiguration->insurances as $insurance) {
                (new MondialRelayHomeDeliveryInsurance())
                    ->setMaxValue($insurance->value)
                    ->setPriceWithTax($insurance->price_with_tax_euro)
                    ->setLevel($insurance->level)
                    ->save();
            }

            if (null === MessageQuery::create()->findOneByName(self::TRACKING_MESSAGE_NAME)) {
                $message = new Message();
                $message
                    ->setName(self::TRACKING_MESSAGE_NAME)
                    ->setHtmlLayoutFileName('')
                    ->setHtmlTemplateFileName(self::TRACKING_MESSAGE_NAME.'.html')
                    ->setTextLayoutFileName('')
                    ->setTextTemplateFileName(self::TRACKING_MESSAGE_NAME.'.txt')
                ;

                $languages = LangQuery::create()->find();

                /** @var Lang $language */
                foreach ($languages as $language) {
                    $locale = $language->getLocale();
                    $message->setLocale($locale);

                    $message->setTitle(
                        Translator::getInstance()->trans('Mondial Relay tracking information', [], self::DOMAIN_NAME, $locale)
                    );

                    $message->setSubject(
                        Translator::getInstance()->trans('Your order has been shipped', [], self::DOMAIN_NAME, $locale)
                    );
                }

                $message->save();
            }

            /* Deploy the module's image */
            $module = $this->getModuleModel();
            if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
                $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
            }
        }
    }

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }

    /**
     * Execute sql files in Config/update/ folder named with module version (ex: 1.0.1.sql).
     *
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in(__DIR__.DS.'Config'.DS.'update');

        $database = new Database($con);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }
    }

    /**
     * This method is called by the Delivery  loop, to check if the current module has to be displayed to the customer.
     * Override it to implements your delivery rules/
     *
     * If you return true, the delivery method will de displayed to the customer
     * If you return false, the delivery method will not be displayed
     *
     *
     * @param Country $country
     * @param State|null $state
     * @return boolean
     */
    public function isValidDelivery(Country $country, State $state = null)
    {
        return !empty($this->getAreaForCountry($country)->getData());
    }

    /**
     * Calculate and return delivery price in the shop's default currency
     *
     *
     * @param Country $country
     * @param State|null $state
     * @return OrderPostage|float             the delivery price
     * @throws DeliveryException if the postage price cannot be calculated.
     */
    public function getPostage(Country $country, State $state = null)
    {
        $request = $this->getRequest();

        $cartWeight = $request->getSession()->getSessionCart($this->getDispatcher())->getWeight();
        $cartAmount = $request->getSession()->getSessionCart($this->getDispatcher())->getTaxedAmount($country, false);

        if (null === $orderPostage = $this->getMinPostage($country, $request->getSession()->getLang()->getLocale(), $cartWeight, $cartAmount)) {
            throw new DeliveryException('Mondial Relay unavailable for your cart weight or delivery country');
        }

        return $orderPostage;
    }

    public function getAreaForCountry(Country $country, State $state = null)
    {
        return AreaQuery::create()
            ->useAreaDeliveryModuleQuery()
            ->filterByDeliveryModuleId(self::getModuleId())
            ->endUse()
            ->useCountryAreaQuery()
            ->filterByCountryId($country->getId())
            ->endUse()
            ->find();
    }

    /**
     * Returns ids of area containing this country and covered by this module.
     *
     * @return array Area ids
     */
    public function getAllAreasForCountry(Country $country)
    {
        $areaArray = [];

        $sql = 'SELECT ca.area_id as area_id FROM country_area ca
               INNER JOIN area_delivery_module adm ON (ca.area_id = adm.area_id AND adm.delivery_module_id = :p0)
               WHERE ca.country_id = :p1';

        $con = Propel::getConnection();

        $stmt = $con->prepare($sql);
        $stmt->bindValue(':p0', $this->getModuleModel()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':p1', $country->getId(), PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $areaArray[] = $row['area_id'];
        }

        return $areaArray;
    }


    /**
     * @param $areaId
     * @param $weight
     * @param $cartAmount
     * @param $deliverModeCode
     *
     * @return mixed
     *
     * @throws DeliveryException
     */
    public static function getPostageAmount($areaId, $weight, $cartAmount = 0)
    {
        $freeshipping = self::getConfigValue("mondial_relay_home_delivery_free_shipping_active", false);
        $freeshippingFrom = self::getConfigValue("mondial_relay_home_delivery_free_shipping_from", null);

        /** Set the initial postage price as 0 */
        $postage = 0;

        /* If free shipping is enabled, skip and return 0 */
        if (!$freeshipping) {
            /* If a min price for general freeshipping is defined and the cart reach this amount, return a postage of 0 */
            if (null !== $freeshippingFrom && $freeshippingFrom <= $cartAmount) {
                return 0;
            }

            $areaFreeshipping = MondialRelayHomeDeliveryAreaFreeshippingQuery::create()
                ->filterByAreaId($areaId)
                ->findOne()
            ;

            if ($areaFreeshipping) {
                $areaFreeshipping = $areaFreeshipping->getCartAmount();
            }

            /* If the cart price is superior to the minimum price for free shipping in the area of the order,
             * return the postage as free.
             */
            if (null !== $areaFreeshipping && $areaFreeshipping <= $cartAmount) {
                return 0;
            }


            /** Search the list of prices and order it in ascending order */
            $areaPrices = MondialRelayHomeDeliveryPriceQuery::create()
                ->filterByAreaId($areaId)
                ->filterByMaxWeight($weight, Criteria::GREATER_EQUAL)
                ->_or()
                ->filterByMaxWeight(null)
            ;

            /** Find the correct postage price for the cart weight and price according to the area and delivery mode in $areaPrices*/
            $firstPrice = $areaPrices->find()
                ->getFirst();

            if (null === $firstPrice) {
                return null;
                //throw new DeliveryException("MondialRelay delivery unavailable for your cart weight or delivery country");
            }

            $postage = $firstPrice->getPriceWithTax();
        }

        return $postage;
    }

    public function getMinPostage($country, $locale, $weight = 0, $amount = 0)
    {
        $minPostage = null;

        $areaIdArray = $this->getAllAreasForCountry($country);

        if (empty($areaIdArray)) {
            throw new DeliveryException('Your delivery country is not covered by Mondial Relay.');
        }

        foreach ($areaIdArray as $areaId) {
            try {
                $postage = self::getPostageAmount($areaId, $weight, $amount);
                if (null === $postage) {
                    continue;
                }
                if ($minPostage === null || $postage < $minPostage) {
                    $minPostage = $postage;
                    if ($minPostage == 0) {
                        break;
                    }
                }
            } catch (\Exception $ex) {
                throw new DeliveryException($ex->getMessage());
            }
        }

        if (null === $minPostage) {
            throw new DeliveryException('Mondial Relay delivery unavailable for your cart weight or delivery country');
        }

        return $this->buildOrderPostage($minPostage, $country, $locale, self::getConfigValue(self::MONDIAL_RELAY_HOME_DELIVERY_TAX_RULE_ID));
    }
}
