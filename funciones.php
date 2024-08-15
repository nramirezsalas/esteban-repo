<?php

// require vendor packages autoload
// require_once dirname(__FILE__) . '/vendor/autoload.php';

// require 3th party packages

require_once dirname(__FILE__) . '/geoplugin.class/geoplugin.class.php';

// require WP updates
require_once dirname(__FILE__) . '/inc/wp-updates.php';

// require theme setting
require_once dirname(__FILE__) . '/inc/wp-settings.php';

// require theme functions
require_once dirname(__FILE__) . '/inc/wp-inc.php';

// walker nav menu
require_once dirname(__FILE__) . '/inc/nav-menu-walker.php';

// require theme functions
require_once dirname(__FILE__) . '/inc/woo.php';

define('VERSION_CSS', '2800.0');

function gabu_register_styles()
{
    wp_enqueue_style('gabu-custom', get_template_directory_uri() . "/app.css", [], VERSION_CSS, 'all');
}

add_action('wp_enqueue_scripts', 'gabu_register_styles');

function gabu_register_scripts()
{
    wp_enqueue_script('gabu-custom', get_template_directory_uri() . "/app.js", [], VERSION_CSS, true);
}

add_action('wp_enqueue_scripts', 'gabu_register_scripts');


/**
 ** Get current template
 **/
function get_current_template()
{
    global $template;

    return str_replace('template-', '', basename($template, '.php'));
}

// add_action( 'wp_footer', 'cf7_redirect' );
function cf7_redirect()
{
    ?>
    <script>
        document.addEventListener('wpcf7mailsent', function (event) {
            window.location = '/gracias/';
        }, false);
    </script>
    <?php
}

// Swap image effect
/*
add_action( 'woocommerce_before_shop_loop_item_title', 'add_on_hover_shop_loop_image' );
function add_on_hover_shop_loop_image() {
  $image_id = wc_get_product()->get_gallery_image_ids()[0];

  echo wp_get_attachment_image( $image_id ?: wc_get_product()->get_image_id() );
}
*/
// Design selector
add_filter('acf/load_field/name=design_id', 'acf_load_designs_field_choices');
function acf_load_designs_field_choices($field)
{
    global $pw_gift_cards_email_designer;
    $field['choices'] = [];
    $choices = $pw_gift_cards_email_designer->get_designs();

    if (is_array($choices)) {
        foreach ($choices as $id => $design_option) {
            $field['choices'][$id] = $design_option['name'];
        }

    }

    return $field;

}

// Scripts
add_action('wp_footer', 'change_gallery_image');
function change_gallery_image()
{ ?>
    <script>
        // Checkout
        <?php if (is_checkout()): ?>
            function setCompanyField(value) {
                if (value === 'Factura') {
                    billing_numero_documento.attr('placeholder', 'RUC')
                    billing_company_field.slideDown()
                } else {
                    billing_numero_documento.attr('placeholder', 'DNI/C.E.')
                    billing_company_field.slideUp()
                }
            }

            const billing_numero_documento = jQuery('#billing_numero_documento')
            const billing_company_field = jQuery('#billing_company_field')
            jQuery('#billing_tipo_comprobante').on('change', function () {
                setCompanyField(jQuery(this).val())
            })
            jQuery(document).ready(function () {
                setCompanyField(jQuery('#billing_tipo_comprobante').val())
            })
        <?php endif; ?>

        //  Product gift card gallery
        <?php if (is_product()): ?>
            jQuery(function ($) {
                const qty = $('form.variations_form .quantity');
                const submitBtn = $('form.variations_form button[type=submit]');
                const originalText = submitBtn.text();
                const yapo = $('#yith-wapo-container');

                function checkBackOrder() {
                    const variationID = $('input[name=variation_id].variation_id').val();
                    const variationData = $('form.variations_form').data("product_variations");
                    $(variationData).each(function (index, variation) {
                        if (variationID == variation.variation_id) {
                            //console.log(variation);
                            qty.hide();
                            setTimeout(function () {
                                // vendido individualmente = para mostrar botón whatsapp PRE-ORDER
                                // por ahora aplica al modelo completo, no por cada variación
                                if (variation.is_sold_individually == "yes") {
                                    qty.fadeOut();
                                    submitBtn.fadeOut();
                                    yapo.fadeOut();

                                    // para todos los idiomas
                                    <?php if (getLanguage() == 'es'): ?>
                                        $('.woocommerce-variation.single_variation').append('<div ' +
                                            'class="woocommerce-variation-description available-in-store"><a href="https://wa.link/8m4ory" target="_blank" ' +
                                            'class="btn-pre-order">CONTACTO</a></div>');
                                    <?php else: ?>
                                        $('.woocommerce-variation.single_variation').append('<div ' +
                                            'class="woocommerce-variation-description available-in-store"><a href="https://wa.link/8m4ory" target="_blank" ' +
                                            'class="btn-pre-order">CONTACT</a></div>');
                                    <?php endif; ?>

                                } else {
                                    // disponible en tienda
                                    if (variation.backorders_allowed) {
                                        qty.fadeOut();
                                        submitBtn.fadeOut();
                                        yapo.fadeOut();
                                        <?php if (getLanguage() == 'es'): ?>
                                            $('.woocommerce-variation.single_variation').append('<div ' +
                                                'class="woocommerce-variation-description available-in-store">DISPONIBLE EN ' +
                                                'TIENDA<br>Realiza tus pedidos y/o consultas al <a href="https://api.whatsapp' +
                                                '.com/send?phone=51980419078" target="_blank" class="numeroTienda">+51 980 419 078</a> o a' +
                                                ' través de nuestras redes sociales.</div>');
                                        <?php else: ?>
                                            $('.woocommerce-variation.single_variation').append('<div class="woocommerce-variation-description available-in-store">AVAILABLE IN STORE<br>Place your orders and make inquiries at <a href="https://api.whatsapp.com/send?phone=51980419078" target="_blank" class="numeroTienda">+51 980 419 078</a> or on our Social Media platforms.</div>');
                                        <?php endif; ?>
                                    } else {
                                        qty.fadeIn();
                                        submitBtn.fadeIn();
                                        yapo.fadeIn();
                                    }
                                }

                            }, 500);
                        }
                    });
                }

                $('input[name=variation_id].variation_id').change(function () {
                    checkBackOrder();
                });
                checkBackOrder();
            });

            const gallery_gift_cards = {};
            <?php while (have_rows('gift_cards', 'options')):
                the_row(); ?>
                gallery_gift_cards[<?php the_sub_field('design_id'); ?>] = '<?php echo get_sub_field('image')['url']; ?>'
            <?php endwhile; ?>
            function gift_card_gallery(url) {
                const variations_form = jQuery('.variations_form');
                const gallery = variations_form.data('product_variations');

                for (let d in gallery) {
                    for (let g in gallery[d]['variation_gallery_images']) {
                        gallery[d]['variation_gallery_images'][g]['src'] = url;
                        gallery[d]['variation_gallery_images'][g]['url'] = url;
                        gallery[d]['variation_gallery_images'][g]['full_src'] = url;
                        gallery[d]['variation_gallery_images'][g]['srcset'] = '';
                    }
                }

                variations_form.data('product_variations', gallery)
                jQuery('.variable-item.selected').click()
            }

            jQuery('#pwgc-email-design-id').on('change', function () {
                const url = gallery_gift_cards[jQuery(this).val()];
                gift_card_gallery(url)
            })
        <?php endif; ?>
        // pa_color ids
        <?php
        $pa_color = get_terms([
            'taxonomy' => 'pa_color',
            'hide_empty' => false,
        ]);
        $pa_color_list = [];
        foreach ($pa_color as $term) {
            $pa_color_list[$term->term_id] = $term->slug;
        }
        ?>
        window.pa_color_ids = <?php echo json_encode($pa_color_list); ?>;
        window.pa_color_selected = [];
        jQuery(document).on('berocket_ajax_products_loaded', function (e) {
            window.pa_color_selected = [];
            const urlParams = new URLSearchParams(window.location.search);
            const filters = urlParams.get('filters');
            if (filters) {
                const matches = filters.match(/\[(.*?)\]/);
                if (matches) {
                    const ids = matches[1].split('-');
                    ids.forEach(function (i) {
                        window.pa_color_selected.push(pa_color_ids[i]);
                    })
                }
            }
        });
        var origOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function () {
            this.addEventListener('load', function () {
                if (this.responseURL.includes('woo_get_variations') && window.pa_color_selected !== []) {
                    window.pa_color_selected.forEach(function (i) {
                        setTimeout(function () {
                            //console.log(i);
                            jQuery('li[data-value=' + i + ']:not(.selected)').click()
                        }, 500)
                    })
                }
            });
            origOpen.apply(this, arguments);
        };
    </script>
    <?php
}

// Backend checkout validation
add_action('woocommerce_after_checkout_validation', 'validate_document', 10, 2);
function validate_document($fields, $errors)
{
    $document = $fields['billing_numero_documento'];
    $type = substr($document, 0, 2);
    $types = ['10', '20'];
    $document_type = $fields['billing_tipo_comprobante'];

    if ($document_type == 'Boleta' && strlen($document) < 8) {
        $errors->add('validation', 'El número de documento debe tener entre 8 y 12 caracteres.');
    } else {
        if ($document_type == 'Factura') {
            if (strlen($document) != 11 || !in_array($type, $types)) {
                $errors->add('validation', 'El RUC debe tener 11 dígitos y comenzar con 10 o 20.');
            }
            if (empty($fields['billing_company'])) {
                $errors->add('validation', 'La razón social es obligatoria.');
            }
        }
    }
}

// Register sidebar
add_action('widgets_init', 'shop_register_sidebars');
function shop_register_sidebars()
{
    register_sidebar(
        [
            'id' => 'shop',
            'name' => __('Shop'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ]
    );
}

/**
 * Added thumbnails for product swatches
 */
// add_action( 'woocommerce_before_shop_loop_item_title', 'show_variations_thumbnails', 20 );
function show_variations_thumbnails()
{
    global $product;

    if ($product->is_type('variable')):
        $variations = $product->get_available_variations();
        $count = 0;
        $additional = 0; ?>
        <div class="list-swatches">
            <?php foreach ($variations as $variation):
                $count++;
                $name = $variation['attributes']['attribute_pa_color'];
                $term = get_term_by('slug', $name, 'pa_color');
                $color = sanitize_hex_color(
                    woo_variation_swatches()->get_frontend()->get_product_attribute_color($term)
                );
                if ($count <= 3): ?>
                    <a class="swatch-link" href="<?php echo $product->get_permalink(); ?>?attribute_pa_color=<?php echo $name ?>">
                        <?php if ($color): ?>
                            <div class="color" style="background: <?= $color ?>;"></div>
                        <?php else: ?>
                            <img src="<?php echo $variation['image']['gallery_thumbnail_src'] ?>" alt="<?php echo $name; ?>"
                                title="<?php echo $name; ?>">
                            <?php echo $color ?>
                        <?php endif; ?>
                    </a>
                <?php else:
                    $additional++;
                endif; ?>
            <?php endforeach;
            if ($additional > 0): ?>
                <div class="additional">
                    +<?php echo $additional; ?>
                    <br>
                    <?php _e('Más', 'skullcandy') ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif;
}

/**
 * Adding .webp extension
 */
function wt_mime_types($mime_types)
{
    $mime_types['webp'] = 'image/webp';

    return $mime_types;
}

add_filter('woocommerce_rest_allowed_image_mime_types', 'wt_mime_types', 1, 1);


/**
 * disable resize default to 2560
 */
add_filter('big_image_size_threshold', '__return_false');


add_filter('woocommerce_variable_sale_price_html', 'lw_variable_product_price', 10, 2);
add_filter('woocommerce_variable_price_html', 'lw_variable_product_price', 10, 2);
function lw_variable_product_price($v_price, $v_product)
{
    // Product Price
    $prod_prices = [$v_product->get_variation_price('min', true), $v_product->get_variation_price('max', true)];
    $prod_price = $prod_prices[0] !== $prod_prices[1] ? sprintf(
        __('%1$s', 'woocommerce'),
        wc_price($prod_prices[0])
    ) : wc_price($prod_prices[0]);
    // Regular Price
    $regular_prices = [
        $v_product->get_variation_regular_price('min', true),
        $v_product->get_variation_regular_price('max', true),
    ];
    sort($regular_prices);
    $regular_price = $regular_prices[0] !== $regular_prices[1] ? sprintf(
        __('%1$s', 'woocommerce'),
        wc_price($regular_prices[0])
    ) : wc_price($regular_prices[0]);
    if ($prod_price !== $regular_price) {
        $prod_price = '<del>' . $regular_price . $v_product->get_price_suffix(
        ) . '</del> <ins>' . $prod_price . $v_product->get_price_suffix() . '</ins>';
    }

    return $prod_price;
}


add_action('woocommerce_review_order_after_submit', 'add_after_checkout_button');

function add_after_checkout_button()
{
    echo the_field('message_under_order_checkout', 'options');
}

add_filter('wt_crp_subcategory_only', '__return_true');

function wp_version_remove_version()
{
    return '';
}

add_filter('the_generator', 'wp_version_remove_version');


/**
 * ocultar departamentos de PERU en el checkout
 */

/*
'01' => 'AMAZONAS',
   '02' => 'ANCASH',
   '03' => 'APURIMAC',
   '04' => 'AREQUIPA',
   '05' => 'AYACUCHO',
   '06' => 'CAJAMARCA',
   '07' => 'CALLAO',
   '08' => 'CUSCO',
   '09' => 'HUANCAVELICA',
   '10' => 'HUANUCO',
   '11' => 'ICA',
   '12' => 'JUNIN',
   '13' => 'LA LIBERTAD',
   '14' => 'LAMBAYEQUE',
   '15' => 'LIMA',
   '16' => 'LORETO',
   '17' => 'MADRE DE DIOS',
   '18' => 'MOQUEGUA',
   '19' => 'PASCO',
   '20' => 'PIURA',
   '21' => 'PUNO',
   '22' => 'SAN MARTIN',
   '23' => 'TACNA',
   '24' => 'TUMBES',
   '25' => 'UCAYALI'
*/
add_filter('woocommerce_states', 'custom_pe_states', 10, 1);
function custom_pe_states($states)
{
    $non_allowed_pe_states = [
        '01',
        '02',
        '03',
        '05',
        '09',
        '10',
        '12',
        '16',
        '17',
        '19',
        '21',
        '22',
        '23',
        '25',
    ];

    foreach ($non_allowed_pe_states as $state_code) {
        if (isset($states['PE'][$state_code])) {
            unset($states['PE'][$state_code]);
        }
    }

    return $states;
}

// add image in email order
add_filter('woocommerce_email_order_items_args', 'custom_email_order_items_args', 10, 1);
function custom_email_order_items_args($args)
{
    $args['show_image'] = true;
    $args['image_size'] = [150, 150];

    return $args;
}

add_filter('woocommerce_order_item_thumbnail', 'add_email_order_item_permalink', 10, 2); // Product image
//add_filter('woocommerce_order_item_name', 'add_email_order_item_permalink', 10, 2); // Product name
function add_email_order_item_permalink($output_html, $item, $bool = false)
{
    // Only email notifications
    if (is_wc_endpoint_url()) {
        return $output_html;
    }

    $product = $item->get_product();

    return '<a href="' . esc_url($product->get_permalink()) . '">' . $output_html . '</a>';
}

/**
 * Selected coupons allowed for logged in users only
 */
/*
add_filter( 'woocommerce_coupon_is_valid', function ( $is_valid, $coupon ) {

   if ( in_array( $coupon->get_code(), [ 'cumple1223' ] ) && !is_user_logged_in() ) {
      return false;
   }

   return $is_valid;
},          100, 2 );

add_filter( 'woocommerce_coupon_error', function ( $message, $error_code, $coupon ) {
   if ( in_array( $coupon->get_code(), [ 'cumple1223' ] ) && !is_user_logged_in() ) {
      $message = '<div>El cupón es válido sólo para usuarios registrados. <a href="' . home_url(
            '/mi-cuenta/'
         ) . '">Ingresar / Registrarse</a></div>';
   }

   return $message;
},          10, 3 );
*/

/**
 * Add custom thumbnails
 */
add_action('after_setup_theme', 'custom_register_thumbnail');
function custom_register_thumbnail()
{
    add_image_size('backend-mini', 0, 100);
    //add_image_size('galery-mini', 120, 80, true,);
}

/**
 * Evitar que wp comprima las imágenes
 */
add_filter('jpeg_quality', function ($arg) {
    return 100;
});

/* get total products */
function getTotalProducts($page, $slug = null)
{
    switch ($page) {
        case 'shop':
            $args = [
                'post_type' => ['product'],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'product_type',
                        'field' => 'slug',
                        'terms' => 'variable', //simple|grouped|external|variable|pw-gift-card
                    ],
                ],
            ];
            break;
        case 'category':
            $args = [
                'post_type' => 'product',
                'product_cat' => $slug,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                //            'meta_query'     => [
//               [
//                  'key'   => '_stock_status',
//                  'value' => 'instock',
//               ],
//               [
//                  'key'   => '_backorders',
//                  'value' => 'notify',
//               ],
//            ],
            ];
            break;

    }
    $products = new WP_Query($args);

    return $products->found_posts;
}

/**
 * Rename product data tabs
 */
add_filter('woocommerce_product_tabs', 'woo_rename_tabs', 98);
function woo_rename_tabs($tabs)
{
    $tabs['description']['title'] = __('Details');
    //$tabs['reviews']['title'] = __( 'Ratings' );
    //$tabs['additional_information']['title'] = __( 'Product Data' );

    return $tabs;
}

/**
 * @snippet       Product Images @ Woo Checkout
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 5
 * @donate        $9     https://businessbloomer.com/bloomer-armada/
 */

add_filter('woocommerce_cart_item_name', 'bbloomer_product_image_review_order_checkout', 9999, 3);

function bbloomer_product_image_review_order_checkout($name, $cart_item, $cart_item_key)
{
    if (!is_checkout()) {
        return $name;
    }
    $product = $cart_item['data'];
    $thumbnail = $product->get_image(['120', '120'], ['class' => 'alignleft']);

    return $thumbnail . $name;
}

/**
 * return current language code
 */
function getLanguage()
{
    return ICL_LANGUAGE_CODE;
}


/**
 * hide buton add to cart to EEUU
 */
add_action('woocommerce_before_single_product', 'hide_add_to_cart_for_specific_product', 10);

function hide_add_to_cart_for_specific_product()
{
    $geoplugin = new geoPlugin();
    //$geoplugin->locate( '167.99.233.87' ); // ip EEUU
    $geoplugin->locate(); // get ip user location
    if ($geoplugin->countryCode == 'US') {
        add_filter('woocommerce_is_purchasable', '__return_false');
    }
}

function agregar_clase_moneda_dolares_al_body($classes)
{
    // Verificar si la moneda actual es dólares
    if (get_woocommerce_currency() === 'USD') {
        // Agregar la clase al array de clases
        $classes[] = 'moneda-usd';
    }
    if (get_woocommerce_currency() === 'PEN') {
        // Agregar la clase al array de clases
        $classes[] = 'moneda-pen';
    }

    return $classes;
}

add_filter('body_class', 'agregar_clase_moneda_dolares_al_body');

function isMobile()
{
    return preg_match(
        "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
        $_SERVER["HTTP_USER_AGENT"]
    );
}

/**
 * aumentar la cantidad de variaciones por página
 */
function handsome_bearded_guy_increase_variations_per_page()
{
    return 50;
}

add_filter('woocommerce_admin_meta_boxes_variations_per_page', 'handsome_bearded_guy_increase_variations_per_page');

/**
 * Fix special charactes problem for AWS search page
 */
add_filter('aws_search_page_posts_objects_ids', 'my_aws_search_page_posts_objects_ids');
function my_aws_search_page_posts_objects_ids($return)
{
    return false;
}

/**
 * retirar precio de resultados de búsqueda en google
 */
function cl_product_delete_meta_price($product = null)
{
    if (!is_object($product)) {
        global $product;
    }

    if (!is_a($product, 'WC_Product')) {
        return;
    }

    if ('' !== $product->get_price()) {
        $shop_name = get_bloginfo('name');
        $shop_url = home_url();

        $markup_offer = array(
            '@type' => 'Offer',
            'availability' => 'https://schema.org/' . $stock = ($product->is_in_stock() ? 'InStock' : 'OutOfStock'),
            'sku' => $product->get_sku(),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'description' => $product->get_description(),
            'seller' => array(
                '@type' => 'Organization',
                'name' => $shop_name,
                'url' => $shop_url,
            ),
        );
    }

    return $markup_offer;
}
add_filter('woocommerce_structured_data_product_offer', 'cl_product_delete_meta_price');
