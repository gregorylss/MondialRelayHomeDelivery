<?php

namespace MondialRelayHomeDelivery\EventListeners;

use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryPriceQuery;
use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\ModuleConfigQuery;

class ConfigListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'module.config' => ['onModuleConfig', 128]
        ];
    }

    public function onModuleConfig(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject !== "HealthStatus") {
            throw new \RuntimeException('Event subject does not match expected value');
        }

        $requiredConfigs = ['mondial_relay_home_delivery_tax_rule_id', 'webservice_url', 'code_enseigne', 'private_key'];

        $shippingZoneConfig = AreaDeliveryModuleQuery::create()
            ->filterByDeliveryModuleId(MondialRelayHomeDelivery::getModuleId())
            ->find();

        $configModule = ModuleConfigQuery::create()
            ->filterByModuleId(MondialRelayHomeDelivery::getModuleId())
            ->find();

        $freeShipping = ModuleConfigQuery::create()
            ->filterByModuleId(MondialRelayHomeDelivery::getModuleId())
            ->filterByName('mondial_relay_home_delivery_free_shipping_active')
            ->findOne();

        $freeShipping = $freeShipping?->getValue();

        $freeShippingFrom = ModuleConfigQuery::create()
            ->filterByModuleId(MondialRelayHomeDelivery::getModuleId())
            ->filterByName('mondial_relay_home_delivery_free_shipping_from')
            ->findOne();

        $freeShippingFrom = $freeShippingFrom?->getValue();

        $deliverySlices = MondialRelayHomeDeliveryPriceQuery::create()
            ->find();

        $moduleConfig['module'] = MondialRelayHomeDelivery::getModuleCode();
        $configsCompleted = true;

        if ($shippingZoneConfig->count() === 0) {
            $configsCompleted = false;
        }

        foreach ($requiredConfigs as $configName) {
            $configFound = false;
            foreach ($configModule as $config) {
                if ($config->getName() === $configName) {
                    $moduleConfig[$configName] = $config->getValue();
                    $configFound = true;
                    if ($config->getValue() === null) {
                        $configsCompleted = false;
                    }
                    break;
                }
            }
            if (!$configFound) {
                $configsCompleted = false;
            }
        }

        $hasFreeShipping = $freeShipping === '1';
        $hasFreeShippingFrom = $freeShippingFrom !== null;
        $hasDeliverySlices = $deliverySlices->count() > 0;

        if (!$hasFreeShipping && !$hasDeliverySlices && !$hasFreeShippingFrom) {
            $configsCompleted = false;
        }

        $moduleConfig['completed'] = $configsCompleted;

        $event->setArgument('mondial_relay_home_delivery.config', $moduleConfig);
    }

}