<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\ProductCommand' ) ) {

/**
 * Manage WooCommerce products.
 *
 * @package Polylang_CLI
 */
class ProductCommand extends BaseCommand {

    /**
     * Syncs a product to all languages.
     *
     * Syncs metadata and taxonomy terms, based on Polylang settings. Run `wp pll option list` to inspect current settings.
     *
     * ## OPTIONS
     *
     * <product_id>
     * : ID of the product to sync. Required.
	 * 
	 * [<language>]
	 * : Language code(s) to sync. All if omitted.
     *
     * ## EXAMPLES
     *
     *     # Sync product 23 (Dutch) to all languages (German and Spanish)
     *     $ wp pll product sync 23
     */
    public function sync( $args, $assoc_args ) {
        global $wpdb;

		$post_id = array_shift($args);
		$selected_languages = $args;
        
        if(!is_numeric($post_id)) {
            $this->cli->error('Post ID is not numeric: ' . $post_id);
        }
        
        $post = get_post($post_id);
        
        if(!$post instanceof \WP_Post) {
            $this->cli->error(sprintf('Post %d not found.', $post_id));
		}
		
		if($post->post_type !== 'product') {
			$this->cli->error(sprintf('Post %d nis not a product.', $post_id));
		}

        if(!function_exists('PLLWC')) {
            $this->cli->error('Missing function PLLWC.');
        }

        PLLWC();
        
        if(!class_exists('PLLWC_Admin_Products')) {
            $this->cli->error('Missing class PLLWC_Admin_Products.');
        }

		$translations = pll_get_post_translations($post_id);

		if(!empty($selected_languages)) {
			foreach(array_keys($translations) as $lang) {
				if(!in_array($lang, $selected_languages)) {
					unset($translations[$lang]);
				}
			}
		}
        
        $this->cli->log(sprintf('Syncing product %d to languages: %s', $post_id, implode(', ', array_keys($translations))));

        do_action('pll_save_post', $post_id, $post, $translations);
        
        $this->cli->success(sprintf('Post %d synced successfully.', $post_id));
    }

}

}
