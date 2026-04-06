<?php

namespace App\Services;

use App\Mail\ContactCustomerMail;
use App\Mail\ContactInternalMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMailNotifier
{
    /**
     * 문의 저장 직후 고객·내부 안내 메일을 발송합니다. 메일 실패 시 DB는 유지하고 로그만 남깁니다.
     */
    public function send(Contact $contact): void
    {
        try {
            Mail::to($contact->email)->send(new ContactCustomerMail($contact));
        } catch (\Throwable $e) {
            Log::error('문의 고객 안내 메일 발송 실패', [
                'contact_id' => $contact->id,
                'message' => $e->getMessage(),
            ]);
        }

        $recipients = config('contact.internal_recipients', []);
        if ($recipients === []) {
            Log::warning('문의 내부 알림 메일 수신자 없음(CONTACT_INTERNAL_MAIL_TO 미설정)');

            return;
        }

        try {
            Mail::to($recipients)->send(new ContactInternalMail($contact));
        } catch (\Throwable $e) {
            Log::error('문의 내부 알림 메일 발송 실패', [
                'contact_id' => $contact->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
