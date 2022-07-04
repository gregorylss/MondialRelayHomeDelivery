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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Thelia\Form\BaseForm;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class PriceAttributesUpdateForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'delivery_time',
                IntegerType::class,
                [
                    "constraints" => [new GreaterThan([ 'value' => 0 ])],
                    'label' => $this->translator->trans('Delivery delay', [], MondialRelayHomeDelivery::DOMAIN_NAME),
                ]
            )
        ;
    }
}
