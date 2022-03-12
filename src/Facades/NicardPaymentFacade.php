<?php

namespace HopeOfIran\NicardPayment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Payment
 *
 * @package Shetabit\Payment\Facade
 *
 * @method static \HopeOfIran\NicardPayment\NicardPayment totalAmount(int $amount)
 * @method static \HopeOfIran\NicardPayment\NicardPayment cashAmount(int $amount)
 * @method static \HopeOfIran\NicardPayment\NicardPayment creditAmount(int $amount)
 * @method static \HopeOfIran\NicardPayment\NicardPayment purchase($finalizeCallback = null)
 * @method static \HopeOfIran\NicardPayment\NicardPayment callbackUrl(string $url)
 * @method static \HopeOfIran\NicardPayment\NicardPayment backUrl(string $url)
 * @method static \Illuminate\Http\Client\Response verify(string $uuidTransaction)
 * @method static \HopeOfIran\NicardPayment\NicardPayment installmentsCountList(array $installmentsCountList = [])
 *
 */
class NicardPaymentFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'nicard-payment';
    }
}
