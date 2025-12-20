<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Confirme seu e-mail | vitrine.top')
            ->greeting('Olá!')
            ->line('Clique no botão abaixo para verificar seu endereço de e-mail.')
            ->action('Verificar e-mail', $url)
            ->line('Se você não criou uma conta, nenhuma ação adicional é necessária.')
            ->salutation('Atenciosamente,')
            ->salutation('vitrine.top');
    }
}
