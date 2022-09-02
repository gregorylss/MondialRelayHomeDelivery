<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayHomeDelivery\Controller\BackOffice;


use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryPrice;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryPriceQuery;
use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class PriceController extends BaseAdminController
{
    public function saveAction($areaId, $moduleId, ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('mondialrelayhomedelivery.prices_update_form');

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            MondialRelayHomeDeliveryPriceQuery::create()->filterByAreaId($areaId)->delete();

            foreach ($data['max_weight'] as $key => $value) {
                (new MondialRelayHomeDeliveryPrice())
                    ->setAreaId($areaId)
                    ->setMaxWeight($value)
                    ->setPriceWithTax($data['price'][$key])
                    ->save();
            }

        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate price form: $errorMessage");
            $form->setErrorMessage($errorMessage);
            $parserContext->addForm($form);
            $parserContext->setGeneralError($errorMessage);

            return $this->render(
                "module-configure",
                ["module_code" => MondialRelayHomeDelivery::getModuleCode()]
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayHomeDelivery'));
    }

    public function createAction($areaId, $moduleId, ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('mondialrelayhomedelivery.price_form');

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            MondialRelayHomeDeliveryPriceQuery::create()->filterByAreaId($areaId)->filterByMaxWeight($data['max_weight'])->delete();

            (new MondialRelayHomeDeliveryPrice())
                ->setAreaId($areaId)
                ->setPriceWithTax($data['price'])
                ->setMaxWeight($data['max_weight'])
                ->save();
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate price form: $errorMessage");
            $form->setErrorMessage($errorMessage);
            $parserContext->addForm($form);
            $parserContext->setGeneralError($errorMessage);

            return $this->render(
                "module-configure",
                ["module_code" => MondialRelayHomeDelivery::getModuleCode()]
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayHomeDelivery'));
    }

    /**
     * @param $insuranceId
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function deleteAction($priceId, $moduleId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::DELETE)) {
            return $response;
        }

        MondialRelayHomeDeliveryPriceQuery::create()->filterById($priceId)->delete();

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayHomeDelivery'));
    }
}
