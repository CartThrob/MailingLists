<?php

namespace CartThrob\MailingLists\Interfaces;

interface MailingListInterface
{
    public function subscribe();

    public function unsubscribe();
}