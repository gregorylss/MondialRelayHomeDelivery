<?php

namespace MondialRelayHomeDelivery\Loop;

use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryFreeshipping;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryFreeshippingQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class FreeShipping extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id')
        );
    }

    public function buildModelCriteria()
    {
        if (null === $isFreeShippingActive = MondialRelayHomeDeliveryFreeshippingQuery::create()->findOneById(1)){
            $isFreeShippingActive = new MondialRelayHomeDeliveryFreeshipping();
            $isFreeShippingActive->setId(1);
            $isFreeShippingActive->setActive(0);
            $isFreeShippingActive->save();
        }

        return MondialRelayHomeDeliveryFreeshippingQuery::create()->filterById(1);
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var MondialRelayHomeDeliveryFreeshipping $freeshipping */
        foreach ($loopResult->getResultDataCollection() as $freeshipping) {
            $loopResultRow = new LoopResultRow($freeshipping);
            $loopResultRow
                ->set('FREESHIPPING_ACTIVE', $freeshipping->getActive())
                ->set('FREESHIPPING_FROM', $freeshipping->getFreeshippingFrom());
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

}