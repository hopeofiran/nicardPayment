# Laravel Parsian Refund 
Laravel Parsian Refund provides amount refundation.

## License
Laravel Persian Validation is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

## Requirement
* Laravel 8.* to up
* PHP 7.3 to up
## Install

Via Composer

``` bash
$ composer require hopeofiran/nicardpayment
```

## Config

Add the following provider to providers part of config/app.php
``` php
HopeOfIran\NicardPayment\Providers\NicardPaymentProvider::class
```

## vendor:publish
You can run vendor:publish command to have custom config file of package on this path ( config/parsianRefund.php )
``` bash
php artisan vendor:publish --provider=HopeOfIran\NicardPayment\Providers\NicardPaymentProvider
```

## Sample code (payment)
``` php
Route::any('/payment', function () {
return \HopeOfIran\NicardPayment\Facades\NicardPaymentFacade::creditAmount(200000)
        ->cashAmount(0)
        ->callbackUrl(\route('payment.verification'))
        ->backUrl(\route('payment.verification'))
        ->installmentsCountList([3, 4])
        ->purchase(function (\HopeOfIran\NicardPayment\NicardPayment $nicardPayment, \Illuminate\Http\Client\Response $response) {
            if ($response->collect()->has('data')) {
                $paymentUrl = $response->collect('data')['open_cpg_url'];
                return $nicardPayment->pay($paymentUrl);
            }
            if ($response->failed()) {
                return $response->collect('errors')->each(function (array $error) {
                    return $error;
                });
            }
            return 'payment failed';
        });
})->name('payment');

```

## Sample code (verification)
``` php
Route::any('/payment/verification', function (\Illuminate\Http\Request $request) {
    $response = \HopeOfIran\NicardPayment\Facades\NicardPaymentFacade::verify($request->input('tid'));
    if ($response->successful()) {
        return $response['is_totally_success'];
    }
    return $response->collect()->get('status');
})->name('payment.verification');
```