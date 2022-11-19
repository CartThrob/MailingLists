<?php

namespace CartThrob\MailingLists\Hooks;

use CartThrob\Hooks\Hook;

class CpMenuArrayHook extends Hook
{
    public function process(array $menu = []): array
    {
        if (ee()->extensions->last_call !== false) {
            $menu = ee()->extensions->last_call;
        }

        $menu[0]['ct.route.nav.addons']['list']['ct.ml.nav'] = [
            'path' => 'addons/settings/cartthrob_mailing_lists',
            'with_base_url' => false,
        ];

        return $menu;
    }
}
