<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: CryptoWoo eCash Add-on
 * Plugin URI: https://github.com/mediaprophet/cryptowoo-ecash-addon
 * GitHub Plugin URI: mediaprophet/cryptowoo-ecash-addon
 * Forked From: CryptoWoo/cryptowoo-dash-addon, Author: flxstn
 * Description: Accept XEC payments in WooCommerce. Requires CryptoWoo main plugin and CryptoWoo HD Wallet Add-on.
 * Version: 1.4.6
 * Author: updated from;  We Program IT | legal company name: OS IT Programming AS | Company org nr: NO 921 074 077
 * Author URI: https://weprogram.it
 * License: GPLv2
 * Text Domain: cryptowoo-xec-addon
 * Domain Path: /lang
 * Tested up to: 5.8.1
 * WC tested up to: 5.7.0
 */
define( 'CWXEC_VER', '1.4.6' );
define( 'CWXEC_FILE', __FILE__ );
add_action( 'wp_enqueue_scripts', 'enqueue_scripts_xec_addon' );

// Load the plugin update library.
add_action( 'cryptowoo_api_manager_loaded', function () {
	new CW_License_Menu( __FILE__, 3358, CWXEC_VER );
} );

/**
 * Plugin activation
 */
function cryptowoo_xec_addon_activate() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$hd_add_on_file = 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php';
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $hd_add_on_file ) || ! file_exists( WP_PLUGIN_DIR . '/cryptowoo/cryptowoo.php' ) ) {

		// If WooCommerce is not installed then show installation notice
		add_action( 'admin_notices', 'cryptowoo_xec_notinstalled_notice' );

		return;
	} elseif ( ! is_plugin_active( $hd_add_on_file ) ) {
		add_action( 'admin_notices', 'cryptowoo_xec_inactive_notice' );

		return;
	}

	if( (defined('CWOO_VERSION' ) && version_compare(CWOO_VERSION, '0.22.0', '<'))  || (defined('HDWALLET_VER' ) && version_compare(HDWALLET_VER, '0.9.1', '<'))) {
		deactivate_plugins( '/cryptowoo-bitcoin-cash-addon/cryptowoo-bitcoin-cash-addon.php', true );
	}
}

register_activation_hook( __FILE__, 'cryptowoo_xec_addon_activate' );
add_action( 'admin_init', 'cryptowoo_xec_addon_activate' );

/**
 * CryptoWoo inactive notice
 */
function cryptowoo_xec_inactive_notice() {

	?>
    <div class="error">
        <p><?php _e( '<b>CryptoWoo ECash add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on has been deactivated.<br>
       				Please go to the Plugins menu and make sure that the CryptoWoo HD Wallet add-on is activated.', 'cryptowoo-xec-addon' ); ?></p>
    </div>
	<?php
}


/**
 * Register and enqueues public-facing JavaScript files.
 */
function enqueue_scripts_xec_addon() {
	if ( is_checkout() ) {
		wp_enqueue_script( 'cryptowoo-xec-addres-format',
			plugins_url( 'js/change-address-format.js', __FILE__ ),
			[ 'wc-checkout', 'jquery' ],
			1
		);
		// https://github.com/bytesofman/xecaddrjs by BytesOfMan
		wp_enqueue_script( 'xecaddr', plugins_url('js/xecaddrjs-0.0.1.min.js', __FILE__), array('wc-checkout', 'jquery'), 1 );

	}
}

/**
 * CryptoWoo HD Wallet add-on not installed notice
 */
function cryptowoo_xec_notinstalled_notice() {
	$addon_link = '<a href="https://www.cryptowoo.com/shop/cryptowoo-hd-wallet-addon/" target="_blank">CryptoWoo HD Wallet add-on</a>';
	?>
    <div class="error">
        <p><?php printf( __( '<b>CryptoWoo ECash add-on error!</b><br>It seems like the CryptoWoo HD Wallet add-on is not installed.<br>
					The CryptoWoo ECash add-on will only work in combination with the CryptoWoo main plugin and the %s.', 'cryptowoo-xec-addon' ), $addon_link ); ?></p>
    </div>
	<?php
}

function cwxec_hd_enabled() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'cryptowoo-hd-wallet-addon/cryptowoo-hd-wallet-addon.php' ) && is_plugin_active( 'cryptowoo/cryptowoo.php' );
}

if ( cwxec_hd_enabled() ) {
	// Coin symbol and name
	add_filter( 'cw_get_cryptocurrencies', 'cwxec_woocommerce_currencies', 10, 1 );
	add_filter( 'cw_get_currency_symbol', 'cwxec_get_currency_symbol', 10, 2 );
	add_filter( 'cw_get_enabled_currencies', 'cwxec_add_coin_identifier', 10, 1 );

	// BIP32 prefixes
	add_filter( 'address_prefixes', 'cwxec_address_prefixes', 10, 1 );

	// Custom block explorer URL
	add_filter( 'cw_link_to_address', 'cwxec_link_to_address', 10, 4 );

	// Options page validations
	add_filter( 'validate_custom_api_genesis', 'cwxec_validate_custom_api_genesis', 10, 2 );
	add_filter( 'validate_custom_api_currency', 'cwxec_validate_custom_api_currency', 10, 2 );
	add_filter( 'cryptowoo_is_ready', 'cwxec_cryptowoo_is_ready', 10, 3 );
	add_filter( 'cw_get_shifty_coins', 'cwxec_cw_get_shifty_coins', 10, 1 );
	add_filter( 'cw_misconfig_notice', 'cwxec_cryptowoo_misconfig_notice', 10, 2 );

	// HD wallet management
	add_filter( 'index_key_ids', 'cwxec_index_key_ids', 10, 1 );
	add_filter( 'mpk_key_ids', 'cwxec_mpk_key_ids', 10, 1 );
	add_filter( 'get_mpk_data_mpk_key', 'cwxec_get_mpk_data_mpk_key', 10, 3 );
	add_filter( 'get_mpk_data_network', 'cwxec_get_mpk_data_network', 10, 3 );
	//ToDo: add_filter( 'cw_blockcypher_currencies', 'cwxec_add_currency_to_array', 10, 1 );
	add_filter( 'cw_discovery_notice', 'cwxec_add_currency_to_array', 10, 1 );

	// Currency params
	add_filter( 'cw_get_currency_params', 'cwxec_get_currency_params', 10, 2 );

	// Order sorting and prioritizing
	add_filter( 'cw_sort_unpaid_addresses', 'cwxec_sort_unpaid_addresses', 10, 2 );
	add_filter( 'cw_prioritize_unpaid_addresses', 'cwxec_prioritize_unpaid_addresses', 10, 2 );
	add_filter( 'cw_filter_batch', 'cwxec_filter_batch', 10, 2 );

	// Add discovery button to currency option
	//add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_xec_mpk', 'hd_wallet_discovery_button' );
	add_filter( 'redux/options/cryptowoo_payments/field/cryptowoo_xec_mpk', 'hd_wallet_discovery_button' );

	// Exchange rates
	add_filter( 'cw_force_update_exchange_rates', 'cwxec_force_update_exchange_rates', 10, 2 );
	add_filter( 'cw_cron_update_exchange_data', 'cwxec_cron_update_exchange_data', 10, 2 );
	add_filter( 'cw_get_bittrex_price_coin', 'cwxec_get_bittrex_price_coin', 10, 1 );

	// Catch failing processing API (only if processing_fallback is enabled)
	add_filter( 'cw_get_tx_api_config', 'cwxec_cw_get_tx_api_config', 10, 3 );

	// Insight API URL
	add_filter( 'cw_prepare_insight_api', 'cwxec_override_insight_url', 10, 4 );

	// Add block explorer processing
	add_filter( 'cw_update_tx_details', 'cwxec_cw_update_tx_details', 10, 5 );

	// Wallet config
	add_filter( 'wallet_config', 'cwxec_wallet_config', 10, 3 );
	add_filter( 'cw_get_processing_config', 'cwxec_processing_config', 10, 3 );

	// Options page
	if( method_exists('Redux', 'set_field') ) {
		// Using Redux v4
		add_action( 'plugins_loaded', 'cwxec_add_fields_v4', 10 );
	} else {
		// Use embedded Redux v3
		add_action( 'plugins_loaded', 'cwxec_add_fields', 10 );  // TODO Remove after main plugin has Redux v4
	}

	// Override Poloniex and Binance to have XECABC instead of XEC as ticker
	add_action( 'cw_exchange_class_name', 'cwxec_override_exchange_name', 10, 2 );
}

/**
 * Override exchanges for XEC (ticker names)
 *
 * @param $exchange_class_name
 * @param $coin_type
 *
 * @return string
 */
function cwxec_override_exchange_name( $exchange_class_name, $coin_type ) {
	if ( 'XEC' === $coin_type && false !== strpos( $exchange_class_name, 'CW_Exchange_' ) ) {
		$exchange_method    = substr( $exchange_class_name, strlen( 'CW_Exchange_' ) );
		$override_exchanges = array( 'poloniex', 'bitfinex', 'binance' );

		if ( in_array( $exchange_method, $override_exchanges ) ) {
			$exchange_class_name = "CW_Exchange_{$exchange_method}_XEC";
			class_exists( $exchange_class_name, false ) ?: require_once plugin_dir_path( __FILE__ ) . strtolower("exchanges/class-cw-exchange-$exchange_method-$coin_type.php");
		}
	}

	return $exchange_class_name;
}

/**
 * Font color for aw-cryptocoins
 * see cryptowoo/assets/fonts/aw-cryptocoins/cryptocoins-colors.css
 */
function cwxec_coin_icon_color() {
	?>
    <style type="text/css">
        i.cc.<?php echo esc_attr( 'XEC' ); ?>, i.cc.<?php echo esc_attr( 'XEC-alt' ); ?> {
            color: #70c659;
        }
    </style>
	<?php
}

add_action( 'wp_head', 'cwxec_coin_icon_color' );

/**
 * Processing API configuration error
 *
 * @param $enabled
 * @param $options
 *
 * @return mixed
 */
function cwxec_cryptowoo_misconfig_notice( $enabled, $options ) {
	$enabled[ 'XEC' ] = $options[ 'processing_api_xec' ] === 'disabled' && ( (bool) CW_Validate::check_if_unset( 'cryptowoo_xec_mpk', $options ) );

	return $enabled;
}

/**
 * Add currency name
 *
 * @param $currencies
 *
 * @return mixed
 */
function cwxec_woocommerce_currencies( $currencies ) {
	$currencies[ 'XEC' ] = __( 'ECash', 'cryptowoo' );

	return $currencies;
}


/**
 * Add currency symbol
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return string
 */
function cwxec_get_currency_symbol( $currency_symbol, $currency ) {
	return $currency === 'XEC' ? 'XEC' : $currency_symbol;
}


/**
 * Add coin identifier
 *
 * @param $coin_identifiers
 *
 * @return array
 */
function cwxec_add_coin_identifier( $coin_identifiers ) {
	$coin_identifiers[ 'XEC' ] = 'xec';

	return $coin_identifiers;
}


/**
 * Add address prefix
 *
 * @param $prefixes
 *
 * @return array
 */
function cwxec_address_prefixes( $prefixes ) {
	$prefixes[ 'XEC' ]          = '00';
	$prefixes[ 'XEC_MULTISIG' ] = '05';

	return $prefixes;
}


/**
 * Add wallet config
 *
 * @param $wallet_config
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwxec_wallet_config( $wallet_config, $currency, $options ) {
	if ( $currency === 'XEC' ) {
		$wallet_config                         = array(
			'coin_client'   => 'bitcoincash',
			'request_coin'  => 'XEC',
			'multiplier'    => (float) $options[ 'multiplier_xec' ],
			'safe_address'  => false,
			'decimals'      => 8,
			'mpk_key'       => 'cryptowoo_xec_mpk',
			'fwd_addr_key'  => 'safe_xec_address',
			'threshold_key' => 'forwarding_threshold_xec'
		);
		$wallet_config[ 'hdwallet' ]           = CW_Validate::check_if_unset( $wallet_config[ 'mpk_key' ], $options, false );
		$wallet_config[ 'coin_protocols' ][]   = 'bitcoincash';
		$wallet_config[ 'forwarding_enabled' ] = false;
	}

	return $wallet_config;
}

/**
 * Add InstantSend and "raw" zeroconf settings to processing config
 *
 * @param $pc_conf
 * @param $currency
 * @param $options
 *
 * @return array
 */
function cwxec_processing_config( $pc_conf, $currency, $options ) {
	if ( $currency === 'XEC' ) {
		$pc_conf[ 'instant_send' ]       = isset( $options[ 'xec_instant_send' ] ) ? (bool) $options[ 'xec_instant_send' ] : false;
		$pc_conf[ 'instant_send_depth' ] = 5; // TODO Maybe add option

		// Maybe accept "raw" zeroconf
		$pc_conf[ 'min_confidence' ] = isset( $options[ 'cryptowoo_xec_min_conf' ] ) && (int) $options[ 'cryptowoo_xec_min_conf' ] === 0 && isset( $options[ 'xec_raw_zeroconf' ] ) && (bool) $options[ 'xec_raw_zeroconf' ] ? 0 : 1;
	}

	return $pc_conf;
}


/**
 * Override links to payment addresses
 *
 * @param $url
 * @param $address
 * @param $currency
 * @param $options
 *
 * @return string
 */
//TODO: Save cash address instead of legacy

function cwxec_link_to_address( $url, $address, $currency, $options ) {
	if ( $currency === 'XEC' ) {
		$url = "https://explorer.bitcoin.com/xec/address/{$address}";
		if ( $options[ 'preferred_block_explorer_xec' ] === 'custom' && isset( $options[ 'custom_block_explorer_xec' ] ) ) {
			$url = preg_replace( '/{{ADDRESS}}/', $address, $options[ 'custom_block_explorer_xec' ] );
			if ( ! wp_http_validate_url( $url ) ) {
				$url = '#';
			}
		} elseif ( $options[ 'preferred_block_explorer_xec' ] === 'blockchair' ) {
			$url     = "https://blockchair.com/bitcoin-cash/address/{$address}";
        }
	}

	return $url;
}

/**
 * Update XEC tx details with insight api
 *
 * @param $batch_data
 * @param $batch_currency
 * @param $orders
 * @param $processing
 * @param $options
 *
 * @return string
 */
function cwxec_cw_update_tx_details( $batch_data, $batch_currency, $orders, $processing, $options ) {
	if ( $batch_currency == "XEC" ) {
		// Blockdozer is down so for now we use explorer.bitcoin.com api instead if blockdozer is still in CW options.
		if ( 'cashexplorer' === $options['processing_api_xec'] || 'blockdozer' === $options['processing_api_xec'] ) {
			$options['custom_api_xec'] = 'https://explorer.api.bitcoin.com/xec/v1';
		} /* else if ( $options[ 'processing_api_xec' ] == "blockdozer" ) {
			$options[ 'custom_api_xec' ] = "http://blockdozer.com/insight-api/";
		} */

		$batch = [];
		foreach ( $orders as $order ) {
			$batch[] = $order->address;
		}

		$batch_data[ $batch_currency ] = CW_Insight::insight_batch_tx_update( "XEC", $batch, $orders, $options );
		usleep( 333333 ); // Max ~3 requests/second TODO remove when we have proper rate limiting
	}

	return $batch_data;
}


/**
 * Override genesis block
 *
 * @param $genesis
 * @param $field_id
 *
 * @return string
 */
function cwxec_validate_custom_api_genesis( $genesis, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_xec', 'processing_fallback_url_xec' ) ) ) {
		$genesis = '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f';
		//$genesis  = '00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048'; // 1
	}

	return $genesis;
}


/**
 * Override custom API currency
 *
 * @param $currency
 * @param $field_id
 *
 * @return string
 */
function cwxec_validate_custom_api_currency( $currency, $field_id ) {
	if ( in_array( $field_id, array( 'custom_api_xec', 'processing_fallback_url_xec' ) ) ) {
		$currency = 'XEC';
	}

	return $currency;
}


/**
 * Add currency to cryptowoo_is_ready
 *
 * @param $enabled
 * @param $options
 * @param $changed_values
 *
 * @return array
 */
function cwxec_cryptowoo_is_ready( $enabled, $options, $changed_values ) {
	$enabled[ 'XEC' ]           = (bool) CW_Validate::check_if_unset( 'cryptowoo_xec_mpk', $options, false );
	$enabled[ 'XEC_transient' ] = (bool) CW_Validate::check_if_unset( 'cryptowoo_xec_mpk', $changed_values, false );

	return $enabled;
}


/**
 * Add currency to is_cryptostore check
 *
 * @param $cryptostore
 * @param $woocommerce_currency
 *
 * @return bool
 */
function cwxec_is_cryptostore( $cryptostore, $woocommerce_currency ) {
	return (bool) $cryptostore ?: $woocommerce_currency === 'XEC';
}

add_filter( 'is_cryptostore', 'cwxec_is_cryptostore', 10, 2 );

/**
 * Add currency to Shifty button option field
 *
 * @param $select
 *
 * @return array
 */
function cwxec_cw_get_shifty_coins( $select ) {
	$select[ 'XEC' ] = sprintf( __( 'Display only on %s payment pages', 'cryptowoo' ), 'ECash' );

	return $select;
}


/**
 * Add HD index key id for currency
 *
 * @param $index_key_ids
 *
 * @return array
 */
function cwxec_index_key_ids( $index_key_ids ) {
	$index_key_ids[ 'XEC' ] = 'cryptowoo_xec_index';

	return $index_key_ids;
}


/**
 * Add HD mpk key id for currency
 *
 * @param $mpk_key_ids
 *
 * @return array
 */
function cwxec_mpk_key_ids( $mpk_key_ids ) {
	$mpk_key_ids[ 'XEC' ] = 'cryptowoo_xec_mpk';

	return $mpk_key_ids;
}


/**
 * Override mpk_key
 *
 * @param $mpk_key
 * @param $currency
 * @param $options
 *
 * @return string
 */
function cwxec_get_mpk_data_mpk_key( $mpk_key, $currency, $options ) {
	if ( $currency === 'XEC' ) {
		$mpk_key = "cryptowoo_xec_mpk";
	}

	return $mpk_key;
}


/**
 * Override mpk_data->network
 *
 * @param $mpk_data
 * @param $currency
 * @param $options
 *
 * @return object
 * @throws Exception
 */
function cwxec_get_mpk_data_network( $mpk_data, $currency, $options ) {
	if ( $currency === 'XEC' ) {
		$mpk_data->network        = BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
		$mpk_data->network_config = new \BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig( $mpk_data->network, [
			$mpk_data->slip132->p2pkh( $mpk_data->bitcoinPrefixes )
		] );
	}

	return $mpk_data;
}

/**
 * Add currency force exchange rate update button
 *
 * @param $results
 *
 * @return array
 */
function cwxec_force_update_exchange_rates( $results ) {
	$results[ 'xec' ] = CW_ExchangeRates::processing()->update_coin_rates( 'XEC', false, true );

	return $results;
}

/**
 * Add currency to background exchange rate update
 *
 * @param $data
 * @param $options
 *
 * @return array
 */
function cwxec_cron_update_exchange_data( $data, $options ) {
	$xec = CW_ExchangeRates::processing()->update_coin_rates( 'XEC', $options );

	// Maybe log exchange rate updates
	if ( CW_AdminMain::logging_is_enabled( 'debug' ) ) {
		if ( $xec[ 'status' ] === 'not updated' || strpos( $xec[ 'status' ], 'disabled' ) ) {
			$data[ 'xec' ] = strpos( $xec[ 'status' ], 'disabled' ) ? $xec[ 'status' ] : $xec[ 'last_update' ];
		} else {
			$data[ 'xec' ] = $xec;
		}
	}

	return $data;
}

/**
 * Override Bittrex coin name (BCC instead of XEC)
 *
 * @param $currency
 *
 * @return string
 */
function cwxec_get_bittrex_price_coin( $currency ) {
	if ( $currency === 'XEC' ) {
		$currency = 'BCC';
	}

	return $currency;
}

/**
 * Add currency to currencies array
 *
 * @param $currencies
 *
 * @return array
 */
function cwxec_add_currency_to_array( $currencies ) {
	$currencies[] = 'XEC';

	return $currencies;
}


/**
 * Override currency params in xpub validation
 *
 * @param $currency_params
 * @param $field_id
 *
 * @return object
 */
function cwxec_get_currency_params( $currency_params, $field_id ) {
	if ( strcmp( $field_id, 'cryptowoo_xec_mpk' ) === 0 ) {
		$currency_params                     = new stdClass();
		$currency_params->strlen             = 111;
		$currency_params->mand_mpk_prefix    = 'xpub';   // bip32.org & Electrum prefix
		$currency_params->mand_base58_prefix = '0488b21e'; // ECash
		$currency_params->currency           = 'XEC';
		$currency_params->index_key          = 'cryptowoo_xec_index';
	}

	return $currency_params;
}

/**
 * Add XEC addresses to sort unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwxec_sort_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'XEC' ) === 0 ) {
		$top_n[ 3 ][ 'XEC' ][] = $address;
	}

	return $top_n;
}

/**
 * Add XEC addresses to prioritize unpaid addresses
 *
 * @param array $top_n
 * @param mixed $address
 *
 * @return array
 */
function cwxec_prioritize_unpaid_addresses( $top_n, $address ) {
	if ( strcmp( $address->payment_currency, 'XEC' ) === 0 ) {
		$top_n[ 3 ][] = $address;
	}

	return $top_n;
}

/**
 * Add XEC addresses to address_batch
 *
 * @param array $address_batch
 * @param mixed $address
 *
 * @return array
 */
function cwxec_filter_batch( $address_batch, $address ) {
	if ( strcmp( $address->payment_currency, 'XEC' ) === 0 ) {
		$address_batch[ 'XEC' ][] = $address->address;
	}

	return $address_batch;
}


/**
 * Fallback on failing API
 *
 * @param $api_config
 * @param $currency
 *
 * @return array
 */
function cwxec_cw_get_tx_api_config( $api_config, $currency ) {
	// ToDo: add Blockcypher
	if ( $currency === 'XEC' ) {
		if ( $api_config->tx_update_api === 'cashexplorer' || $api_config->tx_update_api === 'blockdozer' ) {
			$api_config->tx_update_api   = 'insight';
			$api_config->skip_this_round = false;
		} else {
			$api_config->tx_update_api   = 'cashexplorer';
			$api_config->skip_this_round = false;
		}
	}

	return $api_config;
}

/**
 * Override Insight API URL if no URL is found in the settings
 *
 * @param $insight
 * @param $endpoint
 * @param $currency
 * @param $options
 *
 * @return mixed
 */
function cwxec_override_insight_url( $insight, $endpoint, $currency, $options ) {
	if ( $currency === 'XEC' && isset( $options[ 'processing_fallback_url_xec' ] ) && wp_http_validate_url( $options[ 'processing_fallback_url_xec' ] ) ) {
		$fallback_url = $options[ 'processing_fallback_url_xec' ];
		$urls         = $endpoint ? CW_Formatting::format_insight_api_url( $fallback_url, $endpoint ) : CW_Formatting::format_insight_api_url( $fallback_url, '' );
		$insight->url = $urls[ 'surl' ];
	}

	return $insight;
}

/**
 * Add Redux Framework v4 options
 */
function cwxec_add_fields_v4() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::set_field( 'cryptowoo_payments', 'processing-confirmations', array(
		'id'         => 'cryptowoo_xec_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'XEC' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'ECash', 'ECash' ),
		'default'    => 1,
		'min'        => 0,
		'step'       => 1,
		'max'        => 100,
	) );

	// Enable raw zeroconf
	Redux::set_field( 'cryptowoo_payments','processing-confirmations' , array(
		'id'         => 'xec_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'ECash "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed ECash transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_xec', '=', 'custom'),
			array( 'cryptowoo_xec_min_conf', '=', 0 )
		),
	) );


	// Zeroconf order amount threshold
	Redux::set_field( 'cryptowoo_payments', 'processing-zeroconf', array(
		'id'         => 'cryptowoo_max_unconfirmed_xec',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'ECash', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_xec_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::set_field( 'cryptowoo_payments', 'processing-zeroconf', array(
		'id'         => 'cryptowoo_xec_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_xec_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'ECash' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'ECash' ),
	) );


	/*
	// Remove 3rd party confidence
	Redux::remove_field( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::set_field( 'cryptowoo_payments', 'processing-confidence', array(
			'id'    => 'xec_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_xec', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::set_field( 'cryptowoo_payments', 'processing-confidence', array(
			'id'      => 'min_confidence_xec',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'ECash', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_xec_min_conf', '<', 1),

	));


	Redux::set_field( 'cryptowoo_payments', 'processing-confidence', array(
		'id'      => 'min_confidence_xec_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_xec_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'ECash'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'ECash'),
	));

	// Re-add 3rd party confidence
	Redux::set_field( 'cryptowoo_payments', 'processing-confidence', array(
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the SoChain confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::remove_field( 'cryptowoo_payments', 'blockcypher_token', false );
	// Remove CryptoID token field
	Redux::remove_field( 'cryptowoo_payments', 'cryptoid_api_key', false );

	/*
	 * Processing API
	 */
	Redux::set_field( 'cryptowoo_payments', 'processing-api', array(
		'id'                => 'processing_api_xec',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'ECash' ),
		'options'           => array(
			'cashexplorer' => 'explorer.bitcoin.com',
			//'blockdozer'   => 'Blockdozer.com',
			'custom'       => __( 'Custom (no testnet)', 'cryptowoo' ),
			'disabled'     => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	/*
	 * Processing API custom URL warning
	 */
	Redux::set_field( 'cryptowoo_payments', 'processing-api', array(
		'id'         => 'processing_api_xec_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'processing_api_xec', 'equals', 'custom' ),
			array( 'custom_api_xec', 'equals', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s processing API', 'cryptowoo' ), 'ECash' ),
	) );

	/*
	 * Custom processing API URL
	 */
	Redux::set_field( 'cryptowoo_payments', 'processing-api', array(
		'id'                => 'custom_api_xec',
		'type'              => 'text',
		'title'             => sprintf( __( '%s Insight API URL', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Connect to any %sInsight API%s instance.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttps://explorer.api.bitcoin.com/xec/v1/txs?address=%sRoot URL: %shttps://explorer.api.bitcoin.com/xec/v1/%s', 'cryptowoo-xec-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/',
		'required'          => array( 'processing_api_xec', 'equals', 'custom' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid XEC Insight API URL', 'cryptowoo' ),
		'default'           => '',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );

	// Re-add blockcypher token field
	Redux::set_field( 'cryptowoo_payments', 'processing-api', array(
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account xecboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );
	// Re-add CryptoID token field
	Redux::set_field( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'cryptoid_api_key',
		'type'       => 'text',
		'ajax_save'  => false, // Force page load when this changes
		'desc'       => sprintf(__('%sMore info%s', 'cryptowoo'), '<a href="https://chainz.cryptoid.info/api.dws" title="cryptoID API Docs" target="_blank">', '</a>'),
		'title'      =>  __('cryptoID API Key (required)', 'cryptowoo'),
		'subtitle'   => sprintf(__('Use the API token from your %sCryptoID%s account.', 'cryptowoo'), '<strong><a href="https://chainz.cryptoid.info/api.key.dws" title="Request cryptoID API Key" target="_blank">', '</a></strong>'),
		//'validate_callback' => 'redux_validate_token',
	) );

	// API Resource control information
	Redux::set_field( 'cryptowoo_payments', 'processing-api-resources', array(
		'id'                => 'processing_fallback_url_xec',
		'type'              => 'text',
		'title'             => sprintf( __( 'cashexplorer ECash API Fallback', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Fallback to any %sInsight API%s instance in case the cashexplorer API fails. Retry cashexplorer upon beginning of the next hour. Leave empty to disable.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttps://explorer.api.bitcoin.com/xec/v1/txs?address=1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa%sRoot URL: %shttps://explorer.api.bitcoin.com/xec/v1/%s', 'cryptowoo-xec-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/',
		'required'          => array( 'processing_api_xec', 'equals', 'blockcypher' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid XEC Insight API URL', 'cryptowoo' ),
		'default'           => 'https://explorer.api.bitcoin.com/xec/v1/',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );
	/*
	 * Preferred exchange rate provider
	 */
	Redux::set_field( 'cryptowoo_payments', 'rates-exchange', array(
		'id'                => 'preferred_exchange_xec',
		'type'              => 'select',
		'title'             => 'ECash Exchange (XEC/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sECash to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'coingecko'  => 'CoinGecko',
			'binance'    => 'Binance',
			'coinbase'   => 'Coinbase',
			'bittrex'    => 'Bittrex',
			'poloniex'   => 'Poloniex',
			'bitfinex'   => 'Bitfinex',
			'bitstamp'   => 'Bitstamp',
			'bitpay'     => 'BitPay',
			'shapeshift' => 'ShapeShift',
			'livecoin'   => 'Livecoin',
			'okcoin'     => 'OKCoin.com',
		),
		'default'           => 'coingecko',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::set_field( 'cryptowoo_payments', 'rates-multiplier', array(
		'id'            => 'multiplier_xec',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'ECash' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'ECash' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .001,
		'step'          => .001,
		'max'           => 2,
		'resolution'    => 0.001,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::set_field( 'cryptowoo_payments', 'rewriting', array(
		'id'         => 'preferred_block_explorer_xec',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'ECash' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'ECash' ),
		'desc'       => '',
		'options'    => array(
			'autoselect'   => __( 'Autoselect by processing API', 'cryptowoo' ),
			'cashexplorer' => 'explorer.bitcoin.com',
			//'blockdozer'   => 'blockdozer.com',
			'blockchair' => 'blockchair.com',
			'custom'       => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'cashexplorer',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::set_field( 'cryptowoo_payments', 'rewriting', array(
		'id'         => 'preferred_block_explorer_xec_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_xec', '=', 'custom' ),
			array( 'custom_block_explorer_xec', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'ECash' ),
	) );
	Redux::set_field( 'cryptowoo_payments', 'rewriting', array(
		'id'                => 'custom_block_explorer_xec',
		'type'              => 'text',
		'title'             => sprintf( __( 'Custom %s Block Explorer URL', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => __( 'Link to a block explorer of your choice.', 'cryptowoo' ),
		'desc'              => sprintf( __( 'The URL to the page that displays the information for a single address.%sPlease add %s{{ADDRESS}}%s as placeholder for the cryptocurrency address in the URL.%s', 'cryptowoo' ), '<br><strong>', '<code>', '</code>', '</strong>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/txs?address={$address}',
		'required'          => array( 'preferred_block_explorer_xec', '=', 'custom' ),
		'validate_callback' => 'redux_validate_custom_blockexplorer',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid custom block explorer URL', 'cryptowoo' ),
		'default'           => '',
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::set_field( 'cryptowoo_payments', 'pricing-decimals', array(
		'id'         => 'decimals_XEC',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'ECash' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );


	// Remove Bitcoin testnet
	Redux::removeSection( 'cryptowoo_payments', 'wallets-hdwallet-testnet', false );

	/*
	 * HD wallet section start
	 */
	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'         => 'wallets-hdwallet-xec',
		'type'       => 'section',
		'title'      => __( 'ECash', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-XEC',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'                => 'cryptowoo_xec_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-hd-wallet-addon' ), '<b>XEC "xpub..." ', '</b>' ),
		'desc'              => __( 'ECash HD Wallet Extended Public Key (xpub...)', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		//'required' => array('cryptowoo_xec_mpk', 'equals', ''),
		'placeholder'       => 'xpub...',
		// xpub format
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );
	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'                => 'derivation_path_xec',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'ECash' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section end
	 */
	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::set_field( 'cryptowoo_payments', 'wallets-hdwallet', array(
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}

/**
 * Add Redux options
 * @deprecated Redux framework version 4 update required
 */
function cwxec_add_fields() {
	$woocommerce_currency = get_option( 'woocommerce_currency' );

	/*
	 * Required confirmations
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'cryptowoo_xec_min_conf',
		'type'       => 'spinner',
		'title'      => sprintf( __( '%s Minimum Confirmations', 'cryptowoo' ), 'XEC' ),
		'desc'       => sprintf( __( 'Minimum number of confirmations for <strong>%s</strong> transactions - %s Confirmation Threshold', 'cryptowoo' ), 'ECash', 'ECash' ),
		'default'    => 1,
		'min'        => 0,
		'step'       => 1,
		'max'        => 100,
	) );

	// Enable raw zeroconf
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confirmations',
		'id'         => 'xec_raw_zeroconf',
		'type'       => 'switch',
		'title'      => __( 'ECash "Raw" Zeroconf', 'cryptowoo' ),
		'subtitle'   => __( 'Accept unconfirmed ECash transactions as soon as they are seen on the network.', 'cryptowoo' ),
		'desc'       => sprintf( __( '%sThis practice is generally not recommended. Only enable this if you know what you are doing!%s', 'cryptowoo' ), '<strong>', '</strong>' ),
		'default'    => false,
		'required'   => array(
			//array('processing_api_xec', '=', 'custom'),
			array( 'cryptowoo_xec_min_conf', '=', 0 )
		),
	) );


	// Zeroconf order amount threshold
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_max_unconfirmed_xec',
		'type'       => 'slider',
		'title'      => sprintf( __( '%s zeroconf threshold (%s)', 'cryptowoo' ), 'ECash', $woocommerce_currency ),
		'desc'       => '',
		'required'   => array( 'cryptowoo_xec_min_conf', '<', 1 ),
		'default'    => 100,
		'min'        => 0,
		'step'       => 10,
		'max'        => 500,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-zeroconf',
		'id'         => 'cryptowoo_xec_zconf_notice',
		'type'       => 'info',
		'style'      => 'info',
		'notice'     => false,
		'required'   => array( 'cryptowoo_xec_min_conf', '>', 0 ),
		'icon'       => 'fa fa-info-circle',
		'title'      => sprintf( __( '%s Zeroconf Threshold Disabled', 'cryptowoo' ), 'ECash' ),
		'desc'       => sprintf( __( 'This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo' ), 'ECash' ),
	) );


	/*
	// Remove 3rd party confidence
	Redux::removeField( 'cryptowoo_payments', 'custom_api_confidence', false );

	/*
	 * Confidence warning
	 * /
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
			'id'    => 'xec_confidence_warning',
			'type'  => 'info',
			'title' => __('Be careful!', 'cryptowoo'),
			'style' => 'warning',
			'desc'  => __('Accepting transactions with a low confidence value increases your exposure to double-spend attacks. Only proceed if you don\'t automatically deliver your products and know what you\'re doing.', 'cryptowoo'),
			'required' => array('min_confidence_xec', '<', 95)
	));

	/*
	 * Transaction confidence
	 * /

	Redux::setField( 'cryptowoo_payments', array(
			'section_id'        => 'processing-confidence',
			'id'      => 'min_confidence_xec',
			'type'    => 'switch',
			'title'   => sprintf(__('%s transaction confidence (%s)', 'cryptowoo'), 'ECash', '%'),
			//'desc'    => '',
			'required' => array('cryptowoo_xec_min_conf', '<', 1),

	));


	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-confidence',
		'id'      => 'min_confidence_xec_notice',
		'type'    => 'info',
		'style' => 'info',
		'notice'    => false,
		'required' => array('cryptowoo_xec_min_conf', '>', 0),
		'icon'  => 'fa fa-info-circle',
		'title'   => sprintf(__('%s "Raw" Zeroconf Disabled', 'cryptowoo'), 'ECash'),
		'desc'    => sprintf(__('This option is disabled because you do not accept unconfirmed %s payments.', 'cryptowoo'), 'ECash'),
	));

	// Re-add 3rd party confidence
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-confidence',
		'id'       => 'custom_api_confidence',
		'type'     => 'switch',
		'title'    => __('Third Party Confidence Metrics', 'cryptowoo'),
		'subtitle' => __('Enable this to use the SoChain confidence metrics when accepting zeroconf transactions with your custom Bitcoin, Litecoin, or Dogecoin API.', 'cryptowoo'),
		'default'  => false,
	));
    */

	// Remove blockcypher token field
	Redux::removeField( 'cryptowoo_payments', 'blockcypher_token', false );
	// Remove CryptoID token field
	Redux::removeField( 'cryptowoo_payments', 'cryptoid_api_key', false );

	/*
	 * Processing API
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'processing_api_xec',
		'type'              => 'select',
		'title'             => sprintf( __( '%s Processing API', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Choose the API provider you want to use to look up %s payments.', 'cryptowoo' ), 'ECash' ),
		'options'           => array(
			'cashexplorer' => 'explorer.bitcoin.com',
			//'blockdozer'   => 'Blockdozer.com',
			'custom'       => __( 'Custom (no testnet)', 'cryptowoo' ),
			'disabled'     => __( 'Disabled', 'cryptowoo' ),
		),
		'desc'              => '',
		'default'           => 'disabled',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_processing_api',
		'select2'           => array( 'allowClear' => false ),
	) );

	/*
	 * Processing API custom URL warning
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'processing_api_xec_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'processing_api_xec', 'equals', 'custom' ),
			array( 'custom_api_xec', 'equals', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s processing API', 'cryptowoo' ), 'ECash' ),
	) );

	/*
	 * Custom processing API URL
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'custom_api_xec',
		'type'              => 'text',
		'title'             => sprintf( __( '%s Insight API URL', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Connect to any %sInsight API%s instance.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttps://explorer.api.bitcoin.com/xec/v1/txs?address=%sRoot URL: %shttps://explorer.api.bitcoin.com/xec/v1/%s', 'cryptowoo-xec-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/',
		'required'          => array( 'processing_api_xec', 'equals', 'custom' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid XEC Insight API URL', 'cryptowoo' ),
		'default'           => '',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );

	// Re-add blockcypher token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api',
		'id'                => 'blockcypher_token',
		'type'              => 'text',
		'ajax_save'         => false, // Force page load when this changes
		'desc'              => sprintf( __( '%sMore info%s', 'cryptowoo' ), '<a href="http://dev.blockcypher.com/#rate-limits-and-tokens" title="BlockCypher Docs: Rate limits and tokens" target="_blank">', '</a>' ),
		'title'             => __( 'BlockCypher Token (optional)', 'cryptowoo' ),
		'subtitle'          => sprintf( __( 'Use the API token from your %sBlockCypher%s account.', 'cryptowoo' ), '<strong><a href="https://accounts.blockcypher.com/" title="BlockCypher account xecboard" target="_blank">', '</a></strong>' ),
		'validate_callback' => 'redux_validate_token'
	) );
	// Re-add CryptoID token field
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'processing-api',
		'id'         => 'cryptoid_api_key',
		'type'       => 'text',
		'ajax_save'  => false, // Force page load when this changes
		'desc'       => sprintf(__('%sMore info%s', 'cryptowoo'), '<a href="https://chainz.cryptoid.info/api.dws" title="cryptoID API Docs" target="_blank">', '</a>'),
		'title'      =>  __('cryptoID API Key (required)', 'cryptowoo'),
		'subtitle'   => sprintf(__('Use the API token from your %sCryptoID%s account.', 'cryptowoo'), '<strong><a href="https://chainz.cryptoid.info/api.key.dws" title="Request cryptoID API Key" target="_blank">', '</a></strong>'),
		//'validate_callback' => 'redux_validate_token',
	) );

	// API Resource control information
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'processing-api-resources',
		'id'                => 'processing_fallback_url_xec',
		'type'              => 'text',
		'title'             => sprintf( __( 'cashexplorer ECash API Fallback', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => sprintf( __( 'Fallback to any %sInsight API%s instance in case the cashexplorer API fails. Retry cashexplorer upon beginning of the next hour. Leave empty to disable.', 'cryptowoo' ), '<a href="https://github.com/bitpay/insight-api/" title="Insight API" target="_blank">', '</a>' ),
		'desc'              => sprintf( __( 'The root URL of the API instance:%sLink to address:%shttps://explorer.api.bitcoin.com/xec/v1/txs?address=1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa%sRoot URL: %shttps://explorer.api.bitcoin.com/xec/v1/%s', 'cryptowoo-xec-addon' ), '<p>', '<code>', '</code><br>', '<code>', '</code></p>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/',
		'required'          => array( 'processing_api_xec', 'equals', 'blockcypher' ),
		'validate_callback' => 'redux_validate_custom_api',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid XEC Insight API URL', 'cryptowoo' ),
		'default'           => 'https://explorer.api.bitcoin.com/xec/v1/',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => __( 'Make sure the root URL of the API has a trailing slash ( / ).', 'cryptowoo' ),
		)
	) );
	/*
	 * Preferred exchange rate provider
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rates-exchange',
		'id'                => 'preferred_exchange_xec',
		'type'              => 'select',
		'title'             => 'ECash Exchange (XEC/BTC)',
		'subtitle'          => sprintf( __( 'Choose the exchange you prefer to use to calculate the %sECash to Bitcoin exchange rate%s', 'cryptowoo' ), '<strong>', '</strong>.' ),
		'desc'              => sprintf( __( 'Cross-calculated via BTC/%s', 'cryptowoo' ), $woocommerce_currency ),
		'options'           => array(
			'coingecko'  => 'CoinGecko',
			'binance'    => 'Binance',
			'coinbase'   => 'Coinbase',
			'bittrex'    => 'Bittrex',
			'poloniex'   => 'Poloniex',
			'bitfinex'   => 'Bitfinex',
			'bitstamp'   => 'Bitstamp',
			'bitpay'     => 'BitPay',
			'shapeshift' => 'ShapeShift',
			'livecoin'   => 'Livecoin',
			'okcoin'     => 'OKCoin.com',
		),
		'default'           => 'coingecko',
		'ajax_save'         => false, // Force page load when this changes
		'validate_callback' => 'redux_validate_exchange_api',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * Exchange rate multiplier
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'    => 'rates-multiplier',
		'id'            => 'multiplier_xec',
		'type'          => 'slider',
		'title'         => sprintf( __( '%s exchange rate multiplier', 'cryptowoo' ), 'ECash' ),
		'subtitle'      => sprintf( __( 'Extra multiplier to apply when calculating %s prices.', 'cryptowoo' ), 'ECash' ),
		'desc'          => '',
		'default'       => 1,
		'min'           => .001,
		'step'          => .001,
		'max'           => 2,
		'resolution'    => 0.001,
		'validate'      => 'comma_numeric',
		'display_value' => 'text'
	) );

	/*
	 * Preferred blockexplorer
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_xec',
		'type'       => 'select',
		'title'      => sprintf( __( '%s Block Explorer', 'cryptowoo' ), 'ECash' ),
		'subtitle'   => sprintf( __( 'Choose the block explorer you want to use for links to the %s blockchain.', 'cryptowoo' ), 'ECash' ),
		'desc'       => '',
		'options'    => array(
			'autoselect'   => __( 'Autoselect by processing API', 'cryptowoo' ),
			'cashexplorer' => 'explorer.bitcoin.com',
			//'blockdozer'   => 'blockdozer.com',
			'blockchair' => 'blockchair.com',
			'custom'       => __( 'Custom (enter URL below)' ),
		),
		'default'    => 'cashexplorer',
		'select2'    => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'rewriting',
		'id'         => 'preferred_block_explorer_xec_info',
		'type'       => 'info',
		'style'      => 'critical',
		'icon'       => 'el el-warning-sign',
		'required'   => array(
			array( 'preferred_block_explorer_xec', '=', 'custom' ),
			array( 'custom_block_explorer_xec', '=', '' ),
		),
		'desc'       => sprintf( __( 'Please enter a valid URL in the field below to use a custom %s block explorer', 'cryptowoo' ), 'ECash' ),
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'rewriting',
		'id'                => 'custom_block_explorer_xec',
		'type'              => 'text',
		'title'             => sprintf( __( 'Custom %s Block Explorer URL', 'cryptowoo' ), 'ECash' ),
		'subtitle'          => __( 'Link to a block explorer of your choice.', 'cryptowoo' ),
		'desc'              => sprintf( __( 'The URL to the page that displays the information for a single address.%sPlease add %s{{ADDRESS}}%s as placeholder for the cryptocurrency address in the URL.%s', 'cryptowoo' ), '<br><strong>', '<code>', '</code>', '</strong>' ),
		'placeholder'       => 'https://explorer.api.bitcoin.com/xec/v1/txs?address={$address}',
		'required'          => array( 'preferred_block_explorer_xec', '=', 'custom' ),
		'validate_callback' => 'redux_validate_custom_blockexplorer',
		'ajax_save'         => false,
		'msg'               => __( 'Invalid custom block explorer URL', 'cryptowoo' ),
		'default'           => '',
	) );

	/*
	 * Currency Switcher plugin decimals
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'pricing-decimals',
		'id'         => 'decimals_XEC',
		'type'       => 'select',
		'title'      => sprintf( __( '%s amount decimals', 'cryptowoo' ), 'ECash' ),
		'subtitle'   => '',
		'desc'       => __( 'This option overrides the decimals option of the WooCommerce Currency Switcher plugin.', 'cryptowoo' ),
		'options'    => array(
			2 => '2',
			4 => '4',
			6 => '6',
			8 => '8'
		),
		'default'    => 4,
		'select2'    => array( 'allowClear' => false )
	) );


	// Remove Bitcoin testnet
	Redux::removeSection( 'cryptowoo_payments', 'wallets-hdwallet-testnet', false );

	/*
	 * HD wallet section start
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-xec',
		'type'       => 'section',
		'title'      => __( 'ECash', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'cc-XEC',
		//'subtitle' => __('Use the field with the correct prefix of your Litecoin MPK. The prefix depends on the wallet client you used to generate the key.', 'cryptowoo-hd-wallet-addon'),
		'indent'     => true,
	) );

	/*
	 * Extended public key
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_xec_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'title'             => sprintf( __( '%sprefix%s', 'cryptowoo-hd-wallet-addon' ), '<b>XEC "xpub..." ', '</b>' ),
		'desc'              => __( 'ECash HD Wallet Extended Public Key (xpub...)', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		//'required' => array('cryptowoo_xec_mpk', 'equals', ''),
		'placeholder'       => 'xpub...',
		// xpub format
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );
	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_xec',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'ECash' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	/*
	 * HD wallet section end
	 */
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

	// Re-add Bitcoin testnet section
	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'wallets-hdwallet-testnet',
		'type'       => 'section',
		'title'      => __( 'TESTNET', 'cryptowoo-hd-wallet-addon' ),
		//'required' => array('testmode_enabled','equals','0'),
		'icon'       => 'fa fa-flask',
		'desc'       => __( 'Accept BTC testnet coins to addresses created via a "tpub..." extended public key. (testing purposes only!)<br><b>Depending on the position of the first unused address, it could take a while until your changes are saved.</b>', 'cryptowoo-hd-wallet-addon' ),
		'indent'     => true,
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'cryptowoo_btc_test_mpk',
		'type'              => 'text',
		'ajax_save'         => false,
		'username'          => false,
		'desc'              => __( 'Bitcoin TESTNET extended public key (tpub...)', 'cryptowoo-hd-wallet-addon' ),
		'title'             => __( 'Bitcoin TESTNET HD Wallet Extended Public Key', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_mpk',
		'placeholder'       => 'tpub...',
		'text_hint'         => array(
			'title'   => 'Please Note:',
			'content' => sprintf( __( 'If you enter a used key you will have to run the address discovery process after saving this setting.%sUse a dedicated HD wallet (or at least a dedicated xpub) for your store payments to prevent address reuse.', 'cryptowoo-hd-wallet-addon' ), '<br>' ),
		)
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id'        => 'wallets-hdwallet',
		'id'                => 'derivation_path_btctest',
		'type'              => 'select',
		'subtitle'          => '',
		'title'             => sprintf( __( '%s Derivation Path', 'cryptowoo-hd-wallet-addon' ), 'BTCTEST' ),
		'desc'              => __( 'Change the derivation path to match the derivation path of your wallet client.', 'cryptowoo-hd-wallet-addon' ),
		'validate_callback' => 'redux_validate_derivation_path',
		'options'           => array(
			'0/' => __( 'm/0/i (e.g. Electrum Standard Wallet)', 'cryptowoo-hd-wallet-addon' ),
			'm'  => __( 'm/i (BIP44 Account)', 'cryptowoo-hd-wallet-addon' ),
		),
		'default'           => '0/',
		'select2'           => array( 'allowClear' => false )
	) );

	Redux::setField( 'cryptowoo_payments', array(
		'section_id' => 'wallets-hdwallet',
		'id'         => 'section-end',
		'type'       => 'section',
		'indent'     => false,
	) );

}

