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


use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryInsurance;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryInsuranceQuery;
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
class InsuranceController extends BaseAdminController
{
    public function saveAction(ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('mondialrelayhomedelivery.insurances_update_form');

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            foreach ($data['max_value'] as $key => $value) {
                if (null !== $insurance = MondialRelayHomeDeliveryInsuranceQuery::create()->findPk($key)) {
                    $insurance
                        ->setMaxValue($value)
                        ->setPriceWithTax($data['price_with_tax'][$key])
                        ->save();
                }
            }
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate insurances form: $errorMessage");
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

    public function createAction(ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('mondialrelayhomedelivery.insurance_create_form');

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            MondialRelayHomeDeliveryInsuranceQuery::create()->filterByMaxValue($data['max_value'])->delete();

            (new MondialRelayHomeDeliveryInsurance())
                ->setPriceWithTax($data['price_with_tax'])
                ->setMaxValue($data['max_value'])
                ->save();
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate insurances form: $errorMessage");
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
    public function deleteAction($insuranceId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayHomeDelivery', AccessManager::DELETE)) {
            return $response;
        }

        MondialRelayHomeDeliveryInsuranceQuery::create()->filterById($insuranceId)->delete();

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayHomeDelivery'));
    }
}
