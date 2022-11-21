<?php

namespace CartThrob\MailingLists\Hooks;

use CartThrob\Hooks\Hook;

class CpMenuArrayHook extends Hook
{
    public function process(array $menu = []): array
    {
        $menu = array_shift($menu);

        if (ee()->extensions->last_call !== false) {
            $menu = ee()->extensions->last_call;
        }

        $menu['ct.route.nav.addons']['list']['ct.ml.nav'] = [
            'path' => 'addons/settings/cartthrob_mailing_lists',
            'with_base_url' => false,
        ];

        return $menu;
    }
}
