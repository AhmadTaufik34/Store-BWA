<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Cart;
use App\Models\Transaction;
use App\Models\TransactionDetail;

use Exception;

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        //save user data
        $user = Auth::user();
        $user->update($request->except('total_price'));

        //Proses Checkout
        $code = 'STORE-' . mt_rand(0000,9999);
        $carts = Cart::with(['product','user'])->where('users_id', Auth::user()->id)->get();

        //Transaction create
        $transation = Transaction::create([
            'users_id' => Auth::user()->id,
            'inscurance_price' => 0,
            'shipping_price' => 0,
            'total_price' => $request->total_price,
            'transaction_status' => 'PENDING',
            'code' => $code
        ]);

        foreach ($carts as $cart) {
            $trx = 'TRX-' . mt_rand(0000,9999);

            TransactionDetail::create([
            'transactions_id' => $transation->id,
            'products_id' => $cart->product->id,
            'price' => $cart->product->price,
            'shipping_status' => 'PENDING',
            'resi' => '',
            'code' => $trx
            ]);
        }

        //Delete Cart Data
        Cart::where('users_id', Auth::user()->id)->delete();

        //Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        //buat array untuk dikirim ke midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $code,
                'gross_amount' => (int) $request->total_price
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                "phone"=> Auth::user()->phone_number,
                "billing_address" => [
                    'address' => Auth::user()->address_one,
                ]
            ],
            'enabled_payments' => [
                'cimb_clicks',
                'bca_klikbca', 'bca_klikpay', 'bri_epay', 'echannel', 'permata_va',
                'bca_va', 'bni_va', 'bri_va', 'other_va', 'gopay', 'indomaret',
                'danamon_online', 'akulaku', 'shopeepay', 'kredivo', 'uob_ezpay','bank_transfer'
            ],
            'vtweb' => []
        ];
           
        try {
        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
        
        // Redirect to Snap Payment Page
        return redirect($paymentUrl);
        }
        catch (Exception $e) {
        echo $e->getMessage();
        }
    }

    public function callback(Request $request)
    {
        // set konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        //instance midtrans notification
        $notification = new Notification();

        //Assign ke variabel untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        //cari berdasarkan id
        $transation = Transaction::findOrFail($order_id);

        //handle notif status
        if($status == 'capture') {
            if($type == 'credit_card') {
                if($fraud == 'challange') {
                    $transation->status = 'PENDING';
                }
                else {
                    $transation->status = 'SUCCESS';
                }
            }
        }

        else if($status == 'settlement'){
            $transation->status = 'SUCCESS';
        }

        else if($status == 'pending'){
            $transation->status = 'PENDING';
        }

        else if($status == 'deny'){
            $transation->status = 'CANCELLED';
        }

        else if($status == 'expire'){
            $transation->status = 'CANCELLED';
        }

        else if($status == 'cancel'){
            $transation->status = 'CANCELLED';
        }

        //simpan transaksi
        $transation->save();

        

    }
}
