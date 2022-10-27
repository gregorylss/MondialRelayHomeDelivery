<?php


namespace MondialRelayHomeDelivery\Loop;

use MondialRelayHomeDelivery\Model\MondialRelayHomeDelivery;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryAreaFreeshippingQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class AreaFreeShipping extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('area_id')
        );
    }

    public function buildModelCriteria()
    {
        $areaId = $this->getAreaId();

        $search = MondialRelayHomeDeliveryAreaFreeshippingQuery::create();

        if (null !== $areaId) {
            $search->filterByAreaId($areaId);
        }

        return $search;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var MondialRelayHomeDeliveryAreaFreeshipping $mode */
        foreach ($loopResult->getResultDataCollection() as $mode) {
            $loopResultRow = new LoopResultRow($mode);
            $loopResultRow
                ->set("ID", $mode->getId())
                ->set("AREA_ID", $mode->getAreaId())
                ->set("CART_AMOUNT", $mode->getCartAmount());
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}