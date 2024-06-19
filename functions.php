		<?php
		/**
		 * Theme functions and definitions
		 *
		 * @package HelloElementor
		 */

		if ( ! defined( 'ABSPATH' ) ) {
			exit; // Exit if accessed directly.
		}

		define( 'HELLO_ELEMENTOR_VERSION', '3.0.2' );

		if ( ! isset( $content_width ) ) {
			$content_width = 800; // Pixels.
		}

		if ( ! function_exists( 'hello_elementor_setup' ) ) {
			/**
			 * Set up theme support.
			 *
			 * @return void
			 */
			function hello_elementor_setup() {
				if ( is_admin() ) {
					hello_maybe_update_theme_version_in_db();
				}

				if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
					register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
					register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
				}

				if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
					add_post_type_support( 'page', 'excerpt' );
				}

				if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
					add_theme_support( 'post-thumbnails' );
					add_theme_support( 'automatic-feed-links' );
					add_theme_support( 'title-tag' );
					add_theme_support(
						'html5',
						[
							'search-form',
							'comment-form',
							'comment-list',
							'gallery',
							'caption',
							'script',
							'style',
						]
					);
					add_theme_support(
						'custom-logo',
						[
							'height'      => 100,
							'width'       => 350,
							'flex-height' => true,
							'flex-width'  => true,
						]
					);

					/*
					 * Editor Style.
					 */
					add_editor_style( 'classic-editor.css' );

					/*
					 * Gutenberg wide images.
					 */
					add_theme_support( 'align-wide' );

					/*
					 * WooCommerce.
					 */
					if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
						// WooCommerce in general.
						add_theme_support( 'woocommerce' );
						// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
						// zoom.
						add_theme_support( 'wc-product-gallery-zoom' );
						// lightbox.
						add_theme_support( 'wc-product-gallery-lightbox' );
						// swipe.
						add_theme_support( 'wc-product-gallery-slider' );
					}
				}
			}
		}
		add_action( 'after_setup_theme', 'hello_elementor_setup' );

		function hello_maybe_update_theme_version_in_db() {
			$theme_version_option_name = 'hello_theme_version';
			// The theme version saved in the database.
			$hello_theme_db_version = get_option( $theme_version_option_name );

			// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
			if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
				update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
			}
		}

		if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
			/**
			 * Check whether to display header footer.
			 *
			 * @return bool
			 */
			function hello_elementor_display_header_footer() {
				$hello_elementor_header_footer = true;

				return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
			}
		}

		if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
			/**
			 * Theme Scripts & Styles.
			 *
			 * @return void
			 */
			function hello_elementor_scripts_styles() {
				$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
					wp_enqueue_style(
						'hello-elementor',
						get_template_directory_uri() . '/style' . $min_suffix . '.css',
						[],
						HELLO_ELEMENTOR_VERSION
					);
				}

				if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
					wp_enqueue_style(
						'hello-elementor-theme-style',
						get_template_directory_uri() . '/theme' . $min_suffix . '.css',
						[],
						HELLO_ELEMENTOR_VERSION
					);
				}

				if ( hello_elementor_display_header_footer() ) {
					wp_enqueue_style(
						'hello-elementor-header-footer',
						get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
						[],
						HELLO_ELEMENTOR_VERSION
					);
				}
			}
		}
		add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

		if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
			/**
			 * Register Elementor Locations.
			 *
			 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
			 *
			 * @return void
			 */
			function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
				if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
					$elementor_theme_manager->register_all_core_location();
				}
			}
		}
		add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

		if ( ! function_exists( 'hello_elementor_content_width' ) ) {
			/**
			 * Set default content width.
			 *
			 * @return void
			 */
			function hello_elementor_content_width() {
				$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
			}
		}
		add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

		if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
			/**
			 * Add description meta tag with excerpt text.
			 *
			 * @return void
			 */
			function hello_elementor_add_description_meta_tag() {
				if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
					return;
				}

				if ( ! is_singular() ) {
					return;
				}

				$post = get_queried_object();
				if ( empty( $post->post_excerpt ) ) {
					return;
				}

				echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
			}
		}
		add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

		// Admin notice
		if ( is_admin() ) {
			require get_template_directory() . '/includes/admin-functions.php';
		}

		// Settings page
		require get_template_directory() . '/includes/settings-functions.php';

		// Header & footer styling option, inside Elementor
		require get_template_directory() . '/includes/elementor-functions.php';

		if ( ! function_exists( 'hello_elementor_customizer' ) ) {
			// Customizer controls
			function hello_elementor_customizer() {
				if ( ! is_customize_preview() ) {
					return;
				}

				if ( ! hello_elementor_display_header_footer() ) {
					return;
				}

				require get_template_directory() . '/includes/customizer-functions.php';
			}
		}
		add_action( 'init', 'hello_elementor_customizer' );

		if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
			/**
			 * Check whether to display the page title.
			 *
			 * @param bool $val default value.
			 *
			 * @return bool
			 */
			function hello_elementor_check_hide_title( $val ) {
				if ( defined( 'ELEMENTOR_VERSION' ) ) {
					$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
					if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
						$val = false;
					}
				}
				return $val;
			}
		}
		add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

		/**
		 * BC:
		 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
		 * The following code prevents fatal errors in child themes that still use this function.
		 */
		if ( ! function_exists( 'hello_elementor_body_open' ) ) {
			function hello_elementor_body_open() {
				wp_body_open();
			}
		}

		add_action( 'admin_enqueue_scripts', 'load_admin_style' );
		function load_admin_style() {
		    wp_register_style( 'admin_css', get_template_directory_uri() . '/admin-style.css');
		    wp_enqueue_style( 'admin_css', get_template_directory_uri() . '/admin-style.css');
		}


		/* 	18-06-2024
		*   create featured product field *
		* 
		*/
		require_once(get_stylesheet_directory().'/inc/classes/frontend_class.php');
		// Add custom field to the product data metabox
			function add_custom_checkbox_to_product() {
			    global $woocommerce, $post;
			    
			    echo '<div class="options_group">';
			    
			    woocommerce_wp_checkbox( array(
			        'id'            => '_featured_checkbox',
			        'wrapper_class' => 'show_if_simple',
			        'label'         => __('Featured Checkbox', 'textdomain'),
			        'description'   => __('This is a custom checkbox', 'textdomain')
			    ));
			    
			    echo '</div>';
			    echo $custom_checkbox = get_post_meta( $post->ID, '_featured_checkbox', true );
			}
			add_action( 'woocommerce_product_options_general_product_data', 'add_custom_checkbox_to_product' );

			// Save the custom field value
			function save_custom_checkbox_field( $post_id ) {
			    $custom_checkbox = isset( $_POST['_featured_checkbox'] ) ? 'yes' : 'no';
			    update_post_meta( $post_id, '_featured_checkbox', $custom_checkbox );
			}
			add_action( 'woocommerce_process_product_meta', 'save_custom_checkbox_field' );

			/* */
			function my_custom_admin_menu() {
			    // Add a new top-level menu
			    add_menu_page(
			        __( 'Custom Menu', 'textdomain' ), // Page title
			        'Custom Menu', // Menu title
			        'manage_options', // Capability
			        'custom-menu-slug', // Menu slug
			        'custom_menu_page_content', // Function to display page content
			        'dashicons-admin-generic', // Icon URL (using a Dashicon here)
			        25 // Position (can be adjusted)
			    );

			    // Add a submenu to the new top-level menu
			    add_submenu_page(
			        'custom-menu-slug', // Parent slug
			        __( 'Product Table', 'textdomain' ), // Page title
			        'Product Table', // Menu title
			        'manage_options', // Capability
			        'product-table-slug', // Menu slug
			        'product_table_page_content' // Function to display page content
			    );
			}
			add_action( 'admin_menu', 'my_custom_admin_menu' );

			// Callback function to display content of the custom menu page
			function custom_menu_page_content() {
			    echo '<div class="wrap">';
			    echo '<h1>' . __( 'Custom Menu Page', 'textdomain' ) . '</h1>';
			    echo '<p>' . __( 'This is a custom menu page.', 'textdomain' ) . '</p>';
			    echo '</div>';
			}

			// Callback function to display content of the product table submenu page
			function product_table_page_content() {
			    echo '<div class="wrap">';
			    echo '<h1>' . __( 'Featured Product Table', 'textdomain' ) . '</h1>';
			    echo '<table class="wp-list-table widefat fixed striped">';
			    echo '<thead>';
			    echo '<tr>';
			    echo '<th>' . __( 'Product Image', 'textdomain' ) . '</th>';
			    echo '<th>' . __( 'ID', 'textdomain' ) . '</th>';
			    echo '<th>' . __( 'Name', 'textdomain' ) . '</th>';
			    echo '<th>' . __( 'Price', 'textdomain' ) . '</th>';
			    echo '</tr>';
			    echo '</thead>';
			    echo '<tbody>';

			    // Fetch products using WooCommerce WC_Product_Query
			    $args = array(
				    'post_type'      => 'product',
				    'posts_per_page' => -1,
				    'meta_query'     => array(
				        array(
				            'key'     => '_featured_checkbox',
				            'value'   => 'yes',
				            'compare' => '=',
				        ),
				    ),
				);
			    $products = new WP_Query( $args );
			    foreach ( $products->posts as $key => $product ) {
			    	 $thumbnail_id = get_post_thumbnail_id( $product->ID );
			        $image = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
			        $product_detail = wc_get_product( $product->ID );
			    	// echo '<pre>'; print_r($product); echo '</pre>';
			        echo '<tr>';
			        echo '<td><img src="'.$image.'"></td>';
			        echo '<td>'.$product->ID.'</td>';
			        echo '<td>'.$product->post_title.'</td>';
			        echo '<td>'.$product_detail->get_price_html().'</td>';
			        echo '</tr>';
			    }

			    echo '</tbody>';
			    echo '</table>';
			    echo '</div>';
			   
		    // Product sorting
		    $sortby = isset($_GET['sortby']) ? sanitize_text_field($_GET['sortby']) : '';

		    // Query arguments
		    $args = array(
		        'post_type' => 'product',
		        'posts_per_page' => -1, // Retrieve all products
		        'meta_key' => '_price',
		        'orderby' => 'meta_value_num'
		    );

		    if ($sortby == 'low_to_high') {
		        $args['order'] = 'ASC';
		    } elseif ($sortby == 'high_to_low') {
		        $args['order'] = 'DESC';
		    }

		    // Query products
		    $products = new WP_Query($args);
		    ?>

		    <div class="wrap">
		        <h1><?php esc_html_e('Product Filtter', 'textdomain'); ?></h1>

		        <form method="GET" id="sorting-form">
		            <input type="hidden" name="page" value="product-table-slug">
		            <select name="sortby" id="sortby" onchange="this.form.submit()">
		                <option value=""><?php esc_html_e('Default Sorting', 'textdomain'); ?></option>
		                <option value="low_to_high" <?php selected('low_to_high', $sortby); ?>><?php esc_html_e('Price: Low to High', 'textdomain'); ?></option>
		                <option value="high_to_low" <?php selected('high_to_low', $sortby); ?>><?php esc_html_e('Price: High to Low', 'textdomain'); ?></option>
		            </select>
		        </form>
		        <table class="wp-list-table widefat fixed striped">
		            <thead>
		                <tr>
		                	<th><?php esc_html_e('Product Image', 'textdomain'); ?></th>
		                    <th><?php esc_html_e('Product Name', 'textdomain'); ?></th>
		                    <th><?php esc_html_e('Price', 'textdomain'); ?></th>
		                </tr>
		            </thead>
		            <tbody>
		                <?php
		                if ($products->have_posts()) :
		                    while ($products->have_posts()) : $products->the_post();
		                        $product = wc_get_product(get_the_ID());
		                        $thumbnail_id = get_post_thumbnail_id( get_the_ID() );
			        			$image = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
		                        ?>
		                        <tr>
		                        	<td><img src="<?php echo $image; ?>"></td>
		                            <td><?php the_title(); ?></td>
		                            <td><?php echo wc_price($product->get_price()); ?></td>
		                        </tr>
		                        <?php
		                    endwhile;
		                    wp_reset_postdata();
		                else :
		                    ?>
		                    <tr>
		                        <td colspan="2"><?php esc_html_e('No products found', 'textdomain'); ?></td>
		                    </tr>
		                    <?php
		                endif;
		                ?>
		            </tbody>
		        </table>
		    </div>
			<?php
			    // Get WooCommerce product categories
		    $args = array(
		        'taxonomy' => 'product_cat',
		        'hide_empty' => true,
		    );
		    $product_categories = get_terms($args);

		    ?>
	    <div class="wrap">
	        <h1><?php esc_html_e('Category Table', 'textdomain'); ?></h1>

	        <table class="wp-list-table widefat fixed striped">
	            <thead>
	                <tr>
	                    <th><?php esc_html_e('Category Name', 'textdomain'); ?></th>
	                    <th><?php esc_html_e('Category Image', 'textdomain'); ?></th>
	                </tr>
	            </thead>
	            <tbody>
	                <?php
	                if (!empty($product_categories) && !is_wp_error($product_categories)) :
	                    foreach ($product_categories as $category) :
	                        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
	                        $image_url = wp_get_attachment_url($thumbnail_id);
	                        ?>
	                        <tr>
	                            <td><?php echo esc_html($category->name); ?></td>
	                            <td>
	                                <?php if ($image_url) : ?>
	                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" style="max-width: 100px; height: auto;">
	                                <?php else : ?>
	                                    <?php esc_html_e('No image', 'textdomain'); ?>
	                                <?php endif; ?>
	                            </td>
	                        </tr>
	                        <?php
	                    endforeach;
	                else :
	                    ?>
	                    <tr>
	                        <td colspan="2"><?php esc_html_e('No categories found', 'textdomain'); ?></td>
	                    </tr>
	                    <?php
	                endif;
	                ?>
	            </tbody>
	        </table>
	    </div>
		    <?php
			}


/* Front End Code */
// Display custom message and set shipping charges based on zip code
function display_custom_message_and_set_shipping_charges() {
    // Get user's shipping zip code
    $user_zip = WC()->customer->get_shipping_postcode();
    
    // Define the zip codes that qualify for free delivery
    $free_delivery_zips = array( '380060', '360061' );

    // Check if user's zip code qualifies for free delivery
    if ( in_array( $user_zip, $free_delivery_zips ) ) {
        echo '<tr class="shipping-free-message">';
        echo '<td colspan="2" class="text-center">' . __( "In your location delivery is free for now. Enjoy free delivery!", 'textdomain' ) . '</td>';
        echo '</tr>';

        // Set shipping charges to 0
        WC()->cart->set_shipping_total( 0 );
        WC()->cart->calculate_totals();
    }
}
add_action( 'woocommerce_cart_totals_after_shipping', 'display_custom_message_and_set_shipping_charges', 10 );




