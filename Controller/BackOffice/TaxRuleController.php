<?php

namespace MondialRelayHomeDelivery\Controller\BackOffice;

use MondialRelayHomeDelivery\Form\TaxRuleForm;
use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

#[Route('/admin/module/MondialRelayHomeDelivery/tax_rule', name: 'mondial_relay_home_delivery_tax_rule_')]
class TaxRuleController extends BaseAdminController
{
    #[Route('/save', name: 'save')]
    public function saveTaxRule()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, MondialRelayHomeDelivery::DOMAIN_NAME, AccessManager::UPDATE)) {
            return $response;
        }

        $taxRuleForm = $this->createForm(TaxRuleForm::getName());

        $message = false;

        $url = '/admin/module/MondialRelayHomeDelivery';

        try {
            $form = $this->validateForm($taxRuleForm);

            // Get the form field values
            $data = $form->getData();

            MondialRelayHomeDelivery::setConfigValue(MondialRelayHomeDelivery::MONDIAL_RELAY_HOME_DELIVERY_TAX_RULE_ID, $data["tax_rule_id"]);

        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }

        if ($message !== false) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans('Error', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                $message,
                $taxRuleForm,
                $ex
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl($url, [ 'tab' => 'tax_rule']));
    }
}