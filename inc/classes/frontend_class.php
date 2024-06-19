		<?php 
		class Custom_Ajax_Product_Filter {
		    public function __construct() {
		    	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		        // Hook to add the dropdown to the shop page
		        add_action('woocommerce_before_shop_loop', array($this, 'add_filter_dropdown'), 20);
		        // Enqueue the AJAX script
		        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		        // Handle AJAX request
		        add_action('wp_ajax_custom_filter_products', array($this, 'filter_products'));
		        add_action('wp_ajax_nopriv_custom_filter_products', array($this, 'filter_products'));
		        //add_action( 'template_redirect', array($this, 'redirect_to_checkout'));
		        add_action( 'woocommerce_after_add_to_cart_button', array($this, 'add_gift_card_message_field'));
		       add_filter( 'woocommerce_package_rates', array($this, 'add_multiple_shipping_options'));
		       add_action( 'woocommerce_thankyou', array($this, 'display_custom_message_for_may_month'));
		       add_filter( 'woocommerce_email_recipient_new_order', array($this, 'add_user_email_recipient_to_new_order'));

		       
		    }

		    public function add_filter_dropdown() {
		    	$categories = get_terms(array(
		            'taxonomy' => 'product_cat',
		            'hide_empty' => true,
		        ));
		        ?>
		        <form id="custom-filter-form">
		            <select id="custom-filter" name="custom_filter">
		                <option value="default"><?php _e('Default', 'textdomain'); ?></option>
		                <option value="popular"><?php _e('Popular products', 'textdomain'); ?></option>
		                <option value="featured"><?php _e('Featured products', 'textdomain'); ?></option>
		                 <?php
		                foreach ($categories as $category) {
		                    // Get category thumbnail ID
		                    $thumbnail_id = get_woocommerce_term_meta($category->term_id, 'thumbnail_id', true);
		                    // Get category thumbnail URL
		                    $image = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
		                    ?>
		                    <option value="<?php echo esc_attr($category->slug); ?>" data-image="<?php echo esc_url($image[0]); ?>">
		                        <?php echo esc_html($category->name); ?>
		                    </option>
		                    <?php
		                }
		                ?>
		            </select>
		        </form>
		        <?php
		    }

		    public function enqueue_scripts() {
		        if (is_shop()) {
		            wp_enqueue_script('custom-filter-script', get_template_directory_uri() . '/js/custom-filtter.js', array('jquery'), null, true);

		            wp_localize_script('custom-filter-script', 'ajax_params', array(
		                'ajax_url' => admin_url('admin-ajax.php')
		            ));
		        }
		        if ( is_product() ) {
			        wp_enqueue_script('custom-filter-script', get_template_directory_uri() . '/js/custom-filtter.js', array('jquery'), null, true);
			    }
		    }

		    public function filter_products() {
		        $filter = sanitize_text_field($_POST['filter']);
		        $args = array(
		            'post_type' => 'product',
		            'posts_per_page' => -1,
		        );

		        switch ($filter) {
		            case 'popular':
		                $args['meta_key'] = 'total_sales';
		                $args['orderby'] = 'meta_value_num';
		                break;
		            case 'featured':
		                $this->add_featured_filter($args);
		                break;
		            case 'new':
		                $this->add_category_filter($args);
		                break;
		            case 'old':
		                $this->add_category_filter($args);
		                break;
		            case 'default':
		            default:
		                // No additional args needed for default
		                break;
		        }

		        $query = new WP_Query($args);

		        if ($query->have_posts()) {
		            while ($query->have_posts()) : $query->the_post();
		                wc_get_template_part('content', 'product');
		            endwhile;
		            wp_reset_postdata();
		        } else {
		            echo '<p>' . __('No products found', 'textdomain') . '</p>';
		        }

		        wp_die();
		    }
		     public function add_featured_filter(&$args) {
		        // Check if the 'featured' key value is 'yes'
		         $args['meta_query'] = array(
		            array(
		                'key' => '_featured_checkbox',
		                'value' => 'yes',
		                'compare' => '=',
		            ),
		        );
		    }
		     public function add_category_filter(&$args) {
		        // Get selected category slug from $_POST
		        $category_slug = sanitize_text_field($_POST['category']);

		        $args['tax_query'] = array(
		            array(
		                'taxonomy' => 'product_cat',
		                'field' => 'slug',
		                'terms' => 'new',
		            ),
		        );
		    }
		    public function redirect_to_checkout(){

		    	  if ( ! WC()->cart->is_empty() ) {
			        // Redirect to checkout
			        $checkout_url = wc_get_checkout_url();
			        wp_redirect( $checkout_url );
			        exit;
			    }
		    }
		    public function add_gift_card_message_field(){
			global $product;
		    $gift_card_message = $product->get_meta( '_gift_card_message' );
		    $maxlength = 140;
		    $remaining = $maxlength - mb_strlen( $gift_card_message );
		    echo '<div class="woocommerce-product-details__gift-card-message">';
		    echo '<label for="gift_card_message">' . __( 'Gift Card Message', 'textdomain' ) . '</label>';
		    echo '<textarea id="gift_card_message" name="gift_card_message" rows="4" maxlength="' . esc_attr( $maxlength ) . '">' . esc_textarea( $gift_card_message ) . '</textarea>';
		    echo '<p class="char-count"><span class="remaining">' . esc_html( $remaining ) . '</span> ' . __( 'characters remaining', 'textdomain' ) . '</p>';
		    echo '</div>';

		    }
		    public function add_multiple_shipping_options(){
		    	$user_zip = WC()->customer->get_shipping_postcode();
			    //print_r($user_zip);
			    if ( '382225' === $user_zip || '382352' === $user_zip ) {
			        // Define multiple shipping options
			        $rates['flat_rate:4'] = new WC_Shipping_Rate( 'flat_rate:4', __( 'Same day delivery', 'textdomain' ), 250, array(), 'flat_rate' );
			        $rates['flat_rate:5'] = new WC_Shipping_Rate( 'flat_rate:5', __( 'Next day Delivery', 'textdomain' ), 199, array(), 'flat_rate' );
			        $rates['store_pickup'] = new WC_Shipping_Rate( 'store_pickup', __( 'Store Pickup (Free)', 'textdomain' ), 0, array(), 'flat_rate' );
			    }else{
			    	$rates['flat_rate:4'] = new WC_Shipping_Rate( 'flat_rate:4', __( 'Same day delivery', 'textdomain' ), 350, array(), 'flat_rate' );
			        $rates['flat_rate:5'] = new WC_Shipping_Rate( 'flat_rate:5', __( 'Next day Delivery', 'textdomain' ), 399, array(), 'flat_rate' );
			        $rates['store_pickup'] = new WC_Shipping_Rate( 'store_pickup', __( 'Store Pickup (Free)', 'textdomain' ), 0, array(), 'flat_rate' );
			    }
			    if ( '380060' === $user_zip || '360061' === $user_zip ) {
			    	$rates = false;
			    	$rates['flat_rate:8'] = new WC_Shipping_Rate( 'flat_rate:8', __( 'In your location delivery is free for now. Enjoy free delivery!', 'textdomain' ), 0, array(), 'flat_rate' );
			    }
			    return $rates;
		    }
		    public function display_custom_message_for_may_month(){
		    	$current_month = date( 'F' );
			    // Check if current month is May
			    if ( 'May' === $current_month ) {
			        echo '<p class="custom-message-for-may">Your order preparing time is 1 to 2 days due to May month.</p>';
			    }
		    }
		    public function add_user_email_recipient_to_new_order(){

		    	echo '<h2>' . __( 'You might also like', 'woocommerce' ) . '</h2>';

		    }
		}

		// Initialize the class
		new Custom_Ajax_Product_Filter();
