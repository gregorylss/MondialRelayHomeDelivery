<?php

namespace MondialRelayHomeDelivery\Form;

use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\TaxRuleI18nQuery;

class TaxRuleForm extends BaseForm
{

    /**
     * @inheritDoc
     */
    protected function buildForm()
    {
        $this->formBuilder
            ->add("tax_rule_id",
                ChoiceType::class,
                [
                    'data' => (int)MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::MONDIAL_RELAY_HOME_DELIVERY_TAX_RULE_ID),
                    'choices' => $this->getTaxRules(),
                    'label' => Translator::getInstance()->trans('Tax Rule', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                ]
            )
        ;
    }

    private function getTaxRules(): array
    {
        $res = [];

        /** @var Request $request */
        $request = $this->request;

        $lang = $request->getSession()?->getAdminEditionLang();

        $taxRules = TaxRuleI18nQuery::create()
            ->filterByLocale($lang->getLocale())
            ->find();

        $res[Translator::getInstance()->trans('Default Tax rule', [], MondialRelayHomeDelivery::DOMAIN_NAME)] = null;

        foreach ($taxRules as $taxRule) {
            $res[$taxRule->getTitle()] = $taxRule->getId();
        }

        return $res;
    }
}