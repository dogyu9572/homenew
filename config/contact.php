<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 문의 접수 내부 알림 수신 주소
    |--------------------------------------------------------------------------
    |
    | 쉼표로 구분하여 여러 주소를 지정할 수 있습니다.
    | 예: CONTACT_INTERNAL_MAIL_TO=dogyu@homepagekorea.com
    | 운영: na@homepagekorea.com,haeun@homepagekorea.com
    |
    */
    'internal_recipients' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CONTACT_INTERNAL_MAIL_TO', ''))
    ))),

];
