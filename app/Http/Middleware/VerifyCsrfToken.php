<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/sales/pdf/{id}', '/quotation/pdf/{id}', 'customerreceipt/pdf/{id}', '/ledger/ledgerPdf/{general_ledger_account_id}/{customer_name}/{type}', 'catalog/list'
    ];
}
