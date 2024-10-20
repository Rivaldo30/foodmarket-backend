<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use App\Models\Transaction;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        //Set konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        //Buat instance midtrans notification
        $notification = new Notifaction();

        //Assign ke variable untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        //Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        //Handel notifikasi status midtrans
        if($status == 'capture'){
            if($type == 'credit_card')
            {
                if($fraud == 'challenge')
                {
                    $transaction->status = 'PENDING';
                }
                else
                {
                    $transaction->status = 'SUCCESS';
                }
            }
        }
            elseif ($status == 'settlement') {
                $transaction->status = 'SUCCESS';
            }

            elseif ($status == 'pending') {
                $transaction->status = 'PENDING';
            }
            
            elseif ($status == 'deny') {
                $transaction->status = 'CANCELED';
            }
            
            elseif ($status == 'expire') {
                $transaction->status = 'CANCELED';
            }
            
            elseif ($status == 'cancel') {
                $transaction->status = 'CANCELED';
            }

        //Simpan transaksi
        $transaction->save();
    }

    public function success()
    {
        return view('midtrans.success');
    }
    public function unfinish()
    {
        return view('midtrans.unfinish');
    }
    public function error()
    {
        return view('midtrans.error');
    }

}
