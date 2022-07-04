<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayHomeDelivery\Hook;

use MondialRelayHomeDelivery\MondialRelayHomeDelivery;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class AdminHookManager extends BaseHook
{
    public function onModuleConfigure(HookRenderEvent $event)
    {
        $vars = [
            'code_enseigne' => MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::CODE_ENSEIGNE),
            'private_key' =>  MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::PRIVATE_KEY),
            'allow_relay_delivery' =>  MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::ALLOW_RELAY_DELIVERY),
            'allow_home_delivery' =>  MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::ALLOW_HOME_DELIVERY),
            'allow_insurance' =>  MondialRelayHomeDelivery::getConfigValue(MondialRelayHomeDelivery::ALLOW_INSURANCE),

            'module_id' =>  MondialRelayHomeDelivery::getModuleId()
        ];

        $event->add(
            $this->render('mondialrelayhomedelivery/module-configuration.html', $vars)
        );
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'tools_mondial_relay',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/MondialRelayHomeDelivery'),
                'title' => $this->trans('Mondial Relay home delivery', [], MondialRelayHomeDelivery::DOMAIN_NAME)
            ]
        );
    }

    public function onModuleConfigureJs(HookRenderEvent $event)
    {
        $event
            ->add($this->render("mondialrelayhomedelivery/assets/js/mondialrelayhomedelivery.js.html"))
            ->add($this->addJS("mondialrelayhomedelivery/assets/js/bootstrap-notify.min.js"))
        ;
    }
}
