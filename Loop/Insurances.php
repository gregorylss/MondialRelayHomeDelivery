<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayHomeDelivery\Loop;

use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryInsurance;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryInsuranceQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class Insurances
 * @package MondialRelayHomeDelivery\Loop
 * @method int getAreaId()
 */
class Insurances extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
        );
    }


    public function buildModelCriteria()
    {
        $query = MondialRelayHomeDeliveryInsuranceQuery::create();

        $query->orderByMaxValue();

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var MondialRelayHomeDeliveryInsurance $item */
        foreach ($loopResult->getResultDataCollection() as $item) {
            $loopResultRow = new LoopResultRow($item);

            $loopResultRow
                ->set('ID', $item->getId())
                ->set('MAX_VALUE', $item->getMaxValue())
                ->set('PRICE', $item->getPriceWithTax())
                ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
