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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Thelia\Form\BaseForm;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class PricesUpdateForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'max_weight',
                CollectionType::class,
                [
                    "entry_type" => NumberType::class,
                    "constraints" => [new GreaterThan([ 'value' => 0 ])],
                    'label' => $this->translator->trans('Weight up to...', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )->add(
                'price',
                CollectionType::class,
                [
                    "entry_type" => NumberType::class,
                    "constraints" => [new GreaterThan([ 'value' => 0 ])],
                    'label' => $this->translator->trans('Price', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }
}
