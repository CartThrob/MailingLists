<?php

namespace CartThrob\MailingLists\Services;

use CartThrob\MailingLists\Services\MailChimpService;

class MailingListService
{
    public $service;

    public function init($service)
    {
        $this->service = new $service();
    }

    public function subscribe()
    {

    }

    public function unsubscribe()
    {

    }
}