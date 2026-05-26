<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ConfigurationController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenant;

        abort_if(! $tenant, 403);

        return view('client.configuracion.index', [
            'tenant' => $tenant,
        ]);
    }
}
