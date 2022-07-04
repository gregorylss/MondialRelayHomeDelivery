<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayHomeDelivery\Form;

use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class SettingsForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                MondialRelayHomeDelivery::CODE_ENSEIGNE,
                TextType::class,
                [
                    "constraints" => [new NotBlank()],
                    'label' => $this->translator->trans('Mondial Relay store code', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('This is the store code, as provided by Mondial Relay.', [], MondialRelayHomeDelivery::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayHomeDelivery::PRIVATE_KEY,
                TextType::class,
                [
                    "constraints" => [new NotBlank()],
                    'label' => $this->translator->trans('Private key', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your private key, as provided by Mondial Relay.', [], MondialRelayHomeDelivery::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayHomeDelivery::ALLOW_INSURANCE,
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => $this->translator->trans('Allow optional insurance', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Check this box to allow an optionnal insurance selection depending on cart value.', [], MondialRelayHomeDelivery::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayHomeDelivery::WEBSERVICE_URL,
                TextType::class,
                [
                    'label' => $this->translator->trans('Mondial Relay Web service WSDL URL', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('This is the URL of the Mondial Relay web service WSDL.', [], MondialRelayHomeDelivery::DOMAIN_NAME)
                    ]
                ]
            );

    }
}
