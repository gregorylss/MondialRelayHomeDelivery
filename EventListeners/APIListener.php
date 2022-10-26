<?php

namespace MondialRelayHomeDelivery\EventListeners;

use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryZoneConfiguration;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryZoneConfigurationQuery;
use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use OpenApi\Events\DeliveryModuleOptionEvent;
use OpenApi\Events\OpenApiEvents;
use OpenApi\Model\Api\DeliveryModuleOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Area;
use Thelia\Module\Exception\DeliveryException;

class APIListener implements EventSubscriberInterface
{
    /** @var ContainerInterface  */
    protected $container;


    /** @var RequestStack  */
    protected $requestStack;

    /**
     * APIListener constructor.
     * @param ContainerInterface $container We need the container because we use a service from another module
     * which is not mandatory, and using its service without it being installed will crash
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public function getDeliveryModuleOptions(DeliveryModuleOptionEvent $deliveryModuleOptionEvent)
    {
        if ($deliveryModuleOptionEvent->getModule()->getId() !== MondialRelayHomeDelivery::getModuleId()) {
            return ;
        }

        $isValid = true;

        $module = new MondialRelayHomeDelivery();
        $country = $deliveryModuleOptionEvent->getCountry();

        $locale = $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale();

        if (empty($countryAreas = $module->getAreaForCountry($country))) {
            throw new DeliveryException(Translator::getInstance()->trans("Your delivery country is not covered by Mondial Relay"));
        }


        /** @var Area $countryArea
         *  @var int $minimumShippingFree
         */
        $minimumShippingFree = null;
        $bestPostage = null;
        foreach ($countryAreas as $area) {
            $orderPostage = $module->getMinPostage(
                $country,
                $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale(),
                $deliveryModuleOptionEvent->getCart()->getWeight()
            );
            $areaConfiguration = MondialRelayHomeDeliveryZoneConfigurationQuery::create()->filterByAreaId($area->getId())->findOne();

            $date = new \DateTime();
            $minimumDeliveryDate = $areaConfiguration ? $date->add(new \DateInterval('P'.$areaConfiguration->getDeliveryTime().'D')) : null;

            if ($minimumShippingFree == null){
                $minimumShippingFree = $orderPostage->getAmount();
                $bestPostage = $orderPostage;
            }
            if ($minimumShippingFree > $orderPostage->getAmount()){
                $bestPostage = $orderPostage;
            }
        }
        /** @var DeliveryModuleOption $deliveryModuleOption */

        $deliveryModuleOption = ($this->container->get('open_api.model.factory'))->buildModel('DeliveryModuleOption');
        $deliveryModuleOption
            ->setCode('MondialRelayHomeDelivery')
            ->setValid($isValid)
            ->setTitle($deliveryModuleOptionEvent->getModule()->setLocale($locale)->getTitle())
            ->setImage('')
            ->setMinimumDeliveryDate($minimumDeliveryDate ? $minimumDeliveryDate->format('d/m/Y') : null)
            ->setMaximumDeliveryDate(null)
            ->setPostage(($bestPostage) ? $bestPostage->getAmount() : 0)
            ->setPostageTax(($bestPostage) ? $bestPostage->getAmountTax() : 0)
            ->setPostageUntaxed(($bestPostage) ? $bestPostage->getAmount() - $bestPostage->getAmountTax() : 0)
        ;
        $deliveryModuleOptionEvent->appendDeliveryModuleOptions($deliveryModuleOption);
    }

    public static function getSubscribedEvents()
    {
        $listenedEvents = [];

        /** Check for old versions of Thelia where the events used by the API didn't exists */
        if (class_exists(DeliveryModuleOptionEvent::class)) {
            $listenedEvents[OpenApiEvents::MODULE_DELIVERY_GET_OPTIONS] = array("getDeliveryModuleOptions", 129);
        }

        return $listenedEvents;
    }
}
