<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Institute Email Domain(s)
    |--------------------------------------------------------------------------
    |
    | Students who register with an email on one of these domains are
    | auto-approved. Everyone else (alumni, or students on other domains)
    | stays in the "pending" state until an admin reviews them.
    |
    | Comma-separated in env, e.g. INSTITUTE_DOMAIN="institute.edu,test.edu"
    |
    */

    'institute_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('INSTITUTE_DOMAIN', 'institute.edu,test.edu'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Contact Address
    |--------------------------------------------------------------------------
    |
    | Shown on the pending-review / access-denied pages so users know how to
    | reach the alumni cell.
    |
    */

    'contact_email' => env('ALMACONNECT_CONTACT_EMAIL', 'alumni-cell@institute.edu'),

];
