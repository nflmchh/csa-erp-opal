<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreOpnameController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $store = $user->primaryStore();

        $storeId = $store?->id;

        return redirect()->route('opname.index', array_filter([
            'location_type' => 'store',
            'location_id'   => $storeId,
        ]));
    }
}
