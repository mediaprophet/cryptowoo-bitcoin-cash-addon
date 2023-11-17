<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}// Exit if accessed directly

/**
 * Binance Exchange Rates Class
 *
 * @category CryptoWoo
 * @package Exchange
 * @subpackage ExchangeBase
 * Author: We Program IT | legal company name: OS IT Programming AS | Company org nr: NO 921 074 077
 * Author URI: https://weprogram.it
 */
class CW_Exchange_Bitfinex_XEC extends CW_Exchange_Bitfinex {
	/** Get the exchange rate pair (base/currency)
	 *
	 * @return string
	 */
	protected function get_search_pair() : string {
		return str_replace( 'xec', 'bab', parent::get_search_pair() );
	}
}