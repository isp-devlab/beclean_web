<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\productCategory;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class DashboardController extends Controller
{
    public function index(){
        $pickup = Schedule::where('user_id', Auth::user()->id)->where('pickup_status', true)->first();
        if($pickup){
            return redirect()->route('dashboard.pickup', $pickup->id);
        }
        $data = [
            'title' => 'Dashboaard',
            'subTitle' => null,
            'category' => productCategory::all(),
            'scheduleCompose' => Schedule::where('date', '<=', Date::now()->format('Y-m-d'))
                                ->where('user_id', Auth::user()->id)
                                ->whereHas('transaction', function ($query) {
                                    $query->where('product_category_id', 2)
                                    ->where('transaction_status', 0);
                                })
                                ->get(),
            'scheduleRecycle' => Schedule::where('date', '<=', Date::now()->format('Y-m-d'))
                                ->where('user_id', Auth::user()->id)
                                ->whereHas('transaction', function ($query) {
                                    $query->where('product_category_id', 1)
                                    ->where('transaction_status', 0);
                                })
                                ->get()         
        ];
        // dd($data['scheduleCompose']);
        return view('dashboard', $data);
    }

    public function pickupAdd(Request $request){
        $id = $request->input('id');
        $schedule = Schedule::findOrFail($id);
        $schedule->pickup_status = true;
        $schedule->save();
        return redirect()->route('dashboard.pickup', $id);
    }

    public function pickup($id){
        $pickup = Schedule::where('user_id', Auth::user()->id)->where('id', $id)->where('pickup_status', true)->first();
        if(!$pickup){
            return redirect()->route('dashboard');
        }
        $data = [
            'title' => 'Pickup',
            'subTitle' => null,
            'schedule' => $pickup,        
        ];
        // dd($data['schedule']->transaction->latitude);
        return view('pages.pickup', $data);

    }

    public function selesai(Request $request, $id){
        $schedule = Schedule::findOrFail($id);
        $schedule->pickup_status = false;
        $schedule->save();

        $transaction = Transaction::findOrFail($schedule->transaction_id);
        $transaction->transaction_status = true;
        $transaction->save();

        return redirect()->route('dashboard');
    }
}
