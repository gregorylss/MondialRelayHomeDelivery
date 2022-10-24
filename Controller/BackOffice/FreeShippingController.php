<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace MondialRelayHomeDelivery\Controller\BackOffice;

use MondialRelayHomeDelivery\Form\FreeShippingForm;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryFreeshipping;
use MondialRelayHomeDelivery\Model\MondialRelayHomeDeliveryFreeshippingQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;

use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use Thelia\Model\AreaQuery;
use Thelia\Tools\URL;

class FreeShippingController extends BaseAdminController
{
    public function toggleFreeShippingActivation()
    {
        if (null !== $response = $this
                ->checkAuth(array(AdminResources::MODULE), array('MondialRelayHomeDelivery'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(FreeShippingForm::getName());
        $response = null;

        try {
            $vform = $this->validateForm($form);
            $freeshipping = $vform->get('freeshipping')->getData();
            $freeshippingFrom = $vform->get('freeshipping_from')->getData();

            if (null === $isFreeShippingActive = MondialRelayHomeDeliveryFreeshippingQuery::create()->findOneById(1)){
                $isFreeShippingActive = new MondialRelayHomeDeliveryFreeshipping();
            }

            $isFreeShippingActive
                ->setActive($freeshipping)
                ->setFreeshippingFrom($freeshippingFrom)
            ;
            $isFreeShippingActive->save();

            $response = $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array (
                    'current_tab'=> 'prices_slices_tab',
                    'module_code'=> 'MondialRelayHomeDelivery',
                    '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction',
                    'price_error_id' => null,
                    'price_error' => null
                )
            );
        } catch (\Exception $e) {
            $response = JsonResponse::create(array('error' => $e->getMessage()), 500);
        }
        return $response;
    }
}
