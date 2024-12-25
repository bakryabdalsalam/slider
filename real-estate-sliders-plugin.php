<?php
/**
 * Plugin Name: Real Estate Sliders Plugin
 * Plugin URI:  https://bakry2.vercel.app/
 * Description: Adds custom shortcodes for Developers, Properties, and Compounds Sliders, including WPML support.
 * Version:     1.0.0
 * Author:      Bakry Abdelsalam
 * Author URI:  https://bakry2.vercel.app/
 * Text Domain: real-estate-sliders
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class Real_Estate_Sliders_Plugin
 */
class Real_Estate_Sliders_Plugin {

    /**
     * Constructor.
     */
    public function __construct() {
        // Enqueue scripts and styles (Owl Carousel, Slick, etc.)
        add_action( 'wp_enqueue_scripts', [ $this, 'register_slider_scripts' ] );

        // Add shortcodes
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Register & enqueue required scripts/styles.
     */
    public function register_slider_scripts() {
        /**
         * Example: If you are using Owl Carousel or other libraries, 
         * you must properly enqueue them here with wp_enqueue_script / wp_enqueue_style.
         */

        // Owl Carousel (adjust paths as needed)
        wp_enqueue_style( 'owl-carousel-css', get_stylesheet_directory_uri() . '/path-to/owl.carousel.min.css', [], '2.3.4' );
        wp_enqueue_style( 'owl-theme-css', get_stylesheet_directory_uri() . '/path-to/owl.theme.default.min.css', [ 'owl-carousel-css' ], '2.3.4' );
        wp_enqueue_script( 'owl-carousel-js', get_stylesheet_directory_uri() . '/path-to/owl.carousel.min.js', [ 'jquery' ], '2.3.4', true );

        // Slick (if used)
        wp_enqueue_style( 'slick-css', get_stylesheet_directory_uri() . '/path-to/slick.css', [], '1.8.1' );
        wp_enqueue_style( 'slick-theme-css', get_stylesheet_directory_uri() . '/path-to/slick-theme.css', [ 'slick-css' ], '1.8.1' );
        wp_enqueue_script( 'slick-js', get_stylesheet_directory_uri() . '/path-to/slick.min.js', [ 'jquery' ], '1.8.1', true );
    }

    /**
     * Register all shortcodes.
     */
    public function register_shortcodes() {
        add_shortcode( 'developers_carousel', [ $this, 'developers_carousel_shortcode' ] );
        add_shortcode( 'developer_slider', [ $this, 'my_developer_slider_shortcode' ] );
        add_shortcode( 'properties_slider', [ $this, 'properties_slider_shortcode' ] );
        add_shortcode( 'compounds_slider', [ $this, 'compounds_slider_shortcode' ] );
    }

    /**
     * Developer Carousel Shortcode
     * Usage: [developers_carousel count="10"]
     */
    public function developers_carousel_shortcode( $atts ) {
        // Inline JS for Owl init
        $inline_js = "
            jQuery(document).ready(function ($) {
                var owl = $('.developer-carousel').owlCarousel({
                    nav: true,
                    dots: true,
                    navText: ['<i class=\"fas fa-caret-left\"></i>', '<i class=\"fas fa-caret-right\"></i>'],
                    loop: true,
                    autoplay: true,
                    autoplayTimeout: 4500,
                    autoplayHoverPause: true,
                    margin: 10,
                    responsive: {
                        0: { items: 1 },
                        480: { items: 1 },
                        768: { items: 1 },
                        992: { items: 4 },
                        1199: { items: 4 }
                    },
                    navContainer: '#dev-nav',
                    dotsContainer: '#dev-dots'
                });

                var currentIndex = 0; // Will hold the current slide index

                // Function to limit dots on mobile
                function limitDotsOnMobile() {
                    var dots = $('#dev-dots .owl-dot');
                    if (window.innerWidth <= 768) {
                        var totalDots = dots.length;
                        dots.hide(); // Hide all dots
                        if (totalDots > 0) { 
                            dots.eq(0).show(); // First dot
                        }
                        if (totalDots > 1) {
                            dots.eq(totalDots - 1).show(); // Last dot
                        }
                        if (currentIndex >= 0 && currentIndex < totalDots) {
                            dots.eq(currentIndex).show(); // Active dot
                        }
                    } else {
                        // Show all dots on larger screens
                        dots.show();
                    }
                }

                owl.on('changed.owl.carousel', function (event) {
                    currentIndex = event.item.index; // Store the current index
                    limitDotsOnMobile();
                });

                $(window).on('resize', function () {
                    limitDotsOnMobile();
                });

                // Initial call after carousel initialization
                var carouselData = owl.data('owl.carousel');
                if (carouselData) {
                    currentIndex = carouselData.relative(carouselData.current());
                }
                limitDotsOnMobile();
            });
        ";
        wp_add_inline_script( 'owl-carousel-js', $inline_js );

        // Extract shortcode attributes
        $atts = shortcode_atts(
            [
                'count' => 10, // Default number of developers
            ],
            $atts,
            'developers_carousel'
        );

        // WP_Query for developers with WPML support
        $args = [
            'post_type'      => 'developer',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
            'suppress_filters' => false, // WPML
        ];
        $query = new WP_Query( $args );

        ob_start();

        if ( $query->have_posts() ) : ?>
            <div class="developer-carousel owl-carousel">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php
                    $developer_title = get_the_title() ? get_the_title() : esc_html__( 'No Title', 'real-estate-sliders' );
                    $developer_image = has_post_thumbnail()
                        ? get_the_post_thumbnail( get_the_ID(), 'medium', ['class' => 'circular-image'] )
                        : '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/placeholder.png' ) . '" alt="Placeholder" class="circular-image">';

                    $developer_link = get_permalink( get_the_ID() );
                    ?>
                    <a href="<?php echo esc_url( $developer_link ); ?>" class="developer-card-link">
                        <div class="developer-card">
                            <div class="developer-card-image">
                                <?php echo $developer_image; ?>
                            </div>
                            <div class="developer-card-details">
                                <h3 class="developer-title">
                                    <?php echo esc_html( $developer_title ); ?>
                                </h3>

                                <!-- Compounds Count -->
                                <div class="developer-compounds-count">
                                    <?php
                                    $compounds_args = [
                                        'post_type'      => 'compound',
                                        'meta_query'     => [
                                            [
                                                'key'     => 'REAL_HOMES_developer',
                                                'value'   => get_the_ID(),
                                                'compare' => '=',
                                            ],
                                        ],
                                        'fields'         => 'ids',
                                        'posts_per_page' => -1,
                                        'suppress_filters' => false, // WPML
                                    ];
                                    $compounds_query = new WP_Query( $compounds_args );
                                    $compounds_count = $compounds_query->found_posts;
                                    wp_reset_postdata();

                                    printf(
                                        esc_html__( '%d Compounds', 'real-estate-sliders' ),
                                        esc_html( $compounds_count )
                                    );
                                    ?>
                                </div>

                                <!-- Property Count -->
                                <div class="developer-compounds-count">
                                    <?php
                                    // Calculate property count
                                    $property_count = 0;
                                    if ( $compounds_count > 0 ) {
                                        foreach ( $compounds_query->posts as $compound_id ) {
                                            $properties_args = [
                                                'post_type'      => 'property',
                                                'meta_query'     => [
                                                    [
                                                        'key'     => 'REAL_HOMES_property_compound',
                                                        'value'   => $compound_id,
                                                        'compare' => '=',
                                                    ],
                                                ],
                                                'fields'         => 'ids',
                                                'posts_per_page' => -1,
                                                'suppress_filters' => false, // WPML
                                            ];
                                            $properties_query = new WP_Query( $properties_args );
                                            $property_count += $properties_query->found_posts;
                                        }
                                    }
                                    printf(
                                        esc_html__( '%d Properties', 'real-estate-sliders' ),
                                        esc_html( $property_count )
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <div id="dev-nav" class="rhea-ultra-carousel-nav-center rhea-ultra-nav-box rhea-ultra-owl-nav owl-nav">
                <div id="dev-dots" class="rhea-ultra-owl-dots owl-dots"></div>
            </div>
        <?php else : ?>
            <p><?php esc_html_e( 'No developers found.', 'real-estate-sliders' ); ?></p>
        <?php endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Slick-based Developer Slider Shortcode
     * Usage: [developer_slider posts_per_page="-1"]
     */
    public function my_developer_slider_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'posts_per_page' => -1,
        ), $atts, 'developer_slider' );

        // Query all developer posts with WPML support
        $args = array(
            'post_type'      => 'developer',
            'posts_per_page' => $atts['posts_per_page'],
            'post_status'    => 'publish',
            'suppress_filters' => false, // WPML
        );

        $query = new WP_Query( $args );

        // If no posts found
        if ( ! $query->have_posts() ) {
            return '<p>' . esc_html__( 'No developers found.', 'real-estate-sliders' ) . '</p>';
        }

        // Initialize Slick slider with inline script
        wp_add_inline_script( 'slick-js', "
            jQuery(document).ready(function($) {
                $('.developer-slider').slick({
                    dots: true,
                    infinite: true,
                    speed: 300,
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    adaptiveHeight: true,
                    arrows: true,
                    autoplay: true,
                    autoplaySpeed: 1000,
                    responsive: [
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1,
                                arrows: false
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1,
                                arrows: false
                            }
                        }
                    ]
                });
            });
        " );

        ob_start();
        ?>
        <div class="developer-slider">
            <?php
            while ( $query->have_posts() ) : $query->the_post();
                $thumb_id = get_post_thumbnail_id( get_the_ID() );
                $img_url  = wp_get_attachment_image_url( $thumb_id, 'large' );

                if ( $img_url ) : ?>
                    <div class="slide-item">
                        <a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title_attribute(); ?>"/>
                        </a>
                    </div>
                <?php endif;
            endwhile; ?>
        </div>
        <?php

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Properties Slider Shortcode (Owl Carousel)
     * Usage: [properties_slider count="6"]
     */
    public function properties_slider_shortcode( $atts ) {
        // Inline JS for Owl init
        $inline_js = "
            jQuery(document).ready(function ($) {
                var owl = $('.properties-slider').owlCarousel({
                    nav: true,
                    dots: true,
                    navText: ['<i class=\"fas fa-caret-left\"></i>', '<i class=\"fas fa-caret-right\"></i>'],
                    loop: true,
                    autoplay: true,
                    autoplayTimeout: 4500,
                    autoplayHoverPause: true,
                    margin: 10,
                    responsive: {
                        0: { items: 1 },
                        480: { items: 2 },
                        768: { items: 3 },
                        992: { items: 3 },
                        1199: { items: 3 }
                    },
                    navContainer: '#properties-nav',
                    dotsContainer: '#properties-dots'
                });

                function limitDotsOnMobile() {
                    var dots = $('#properties-dots .owl-dot');
                    var totalDots = dots.length;

                    if (window.innerWidth <= 768) {
                        dots.hide();
                        dots.eq(0).show();
                        dots.eq(totalDots - 1).show();
                        var activeIndex = $('#properties-dots .owl-dot.active').index();
                        if (activeIndex > 0 && activeIndex < totalDots - 1) {
                            dots.eq(activeIndex).show();
                        }
                    } else {
                        dots.show();
                    }
                }

                owl.on('changed.owl.carousel', function () {
                    limitDotsOnMobile();
                });

                $(window).on('resize', function () {
                    limitDotsOnMobile();
                });

                limitDotsOnMobile();
            });
        ";
        wp_add_inline_script( 'owl-carousel-js', $inline_js );

        // Extract shortcode attributes
        $atts = shortcode_atts(
            [
                'count' => 6,
            ],
            $atts,
            'properties_slider'
        );

        // Query for properties
        $args = [
            'post_type'      => 'property',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
            'suppress_filters' => false, // WPML
        ];
        $query = new WP_Query( $args );

        ob_start();

        if ( $query->have_posts() ) : ?>
            <div class="properties-slider owl-carousel">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <div class="rh-ultra-property-card">
                        <?php
                        /**
                         * Example: If you’re using RealHomes theme partial:
                         * get_template_part( 'assets/ultra/partials/properties/grid-card-' . get_option( 'realhomes_property_card_variation', '1' ) );
                         * Or just show custom markup. For example:
                         */
                        ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'medium' ); ?>
                            <h3><?php the_title(); ?></h3>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            <div id="properties-nav" class="rhea-ultra-carousel-nav-center rhea-ultra-nav-box rhea-ultra-owl-nav owl-nav">
                <div id="properties-dots" class="rhea-ultra-owl-dots owl-dots"></div>
            </div>
        <?php else : ?>
            <p><?php esc_html_e( 'No properties found.', 'real-estate-sliders' ); ?></p>
        <?php endif;

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Compound Slider Shortcode (Owl Carousel)
     * Usage: [compounds_slider count="10"]
     */
    public function compounds_slider_shortcode( $atts ) {
        // Inline JS
        $inline_js = "
            jQuery(document).ready(function ($) {
                var owl = $('.compound-slider').owlCarousel({
                    nav: true,
                    dots: true,
                    navText: ['<i class=\"fas fa-caret-left\"></i>', '<i class=\"fas fa-caret-right\"></i>'],
                    loop: true,
                    autoplay: true,
                    autoplayTimeout: 4500,
                    autoplayHoverPause: true,
                    margin: 10,
                    responsive: {
                        0: { items: 1 },
                        480: { items: 2 },
                        768: { items: 3 },
                        992: { items: 3 },
                        1199: { items: 3 }
                    },
                    navContainer: '#compound-nav',
                    dotsContainer: '#compound-dots'
                });

                function limitDotsOnMobile() {
                    var dots = $('#compound-dots .owl-dot');
                    var totalDots = dots.length;

                    if (window.innerWidth <= 768) {
                        dots.hide();
                        dots.eq(0).show();
                        dots.eq(totalDots - 1).show();
                        var activeIndex = $('#compound-dots .owl-dot.active').index();
                        if (activeIndex > 0 && activeIndex < totalDots - 1) {
                            dots.eq(activeIndex).show();
                        }
                    } else {
                        dots.show();
                    }
                }

                owl.on('changed.owl.carousel', function () {
                    limitDotsOnMobile();
                });

                $(window).on('resize', function () {
                    limitDotsOnMobile();
                });

                limitDotsOnMobile();
            });
        ";
        wp_add_inline_script( 'owl-carousel-js', $inline_js );

        // Extract shortcode attributes
        $atts = shortcode_atts(
            [
                'count' => 10,
            ],
            $atts,
            'compounds_slider'
        );

        // Query arguments for compounds
        $args = [
            'post_type'      => 'compound',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
            'suppress_filters' => false, // WPML
        ];
        $query = new WP_Query( $args );

        ob_start();

        if ( $query->have_posts() ) : ?>
            <div class="compound-slider owl-carousel">
                <?php while ( $query->have_posts() ) : $query->the_post(); 
                    $compound_id = get_the_ID();
                    ?>
                    <div class="rh-ultra-property-card">
                        <!-- Example compound thumbnail -->
                        <div class="rh-ultra-card-thumb-wrapper">
                            <div class="rh-ultra-property-card-thumb">
                                <a class="rh-permalink" href="<?php the_permalink(); ?>">
                                    <?php
                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( 'medium' );
                                    } else {
                                        echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/placeholder.png' ) . '" alt="No Image">';
                                    }
                                    ?>
                                </a>
                            </div>
                            <!-- Example contact icons -->
                            <div class="rh-contact-icons">
                                <a href="tel:+201103131788" class="rh-contact-icon" title="Call">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                                <a href="https://wa.me/+201103131788" target="_blank" class="rh-contact-icon" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                        <!-- Card Details -->
                        <div class="rh-ultra-card-detail-wrapper">
                            <h3 class="rh-ultra-property-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <!-- Price -->
                            <div class="rh_prop_card__priceLabel_ultra">
                                <p class="rh_prop_card__price_ultra">
                                    <span class="ere-price-display">
                                        <?php echo $this->get_property_prices( $compound_id ); ?>
                                    </span>
                                </p>
                            </div>
                            <!-- Property Types -->
                            <div class="rh-properties-card-meta-ultra">
                                <?php echo $this->get_property_types( $compound_id ); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div id="compound-nav" class="rhea-ultra-carousel-nav-center rhea-ultra-nav-box rhea-ultra-owl-nav owl-nav">
                <div id="compound-dots" class="rhea-ultra-owl-dots owl-dots"></div>
            </div>
        <?php else : ?>
            <p><?php esc_html_e( 'No compounds found.', 'real-estate-sliders' ); ?></p>
        <?php endif;

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Helper: Get Property Types
     */
    private function get_property_types( $compound_id ) {
        $original_post = $GLOBALS['post'];

        // Query properties in the current compound
        $compound_properties = new WP_Query([
            'post_type'      => 'property',
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => 'REAL_HOMES_property_compound',
                    'value'   => $compound_id,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => -1,
            'suppress_filters' => false, // WPML
        ]);

        $property_ids = $compound_properties->posts;

        if ( ! empty( $property_ids ) ) {
            // Get all property-type terms associated with these properties
            $terms = wp_get_object_terms( $property_ids, 'property-type' );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                // Example icon map
                $property_type_svgs = [
                    'apartment'   => get_template_directory_uri() . '/assets/ultra/icons/apartment.svg',
                    'cabin'       => get_template_directory_uri() . '/assets/ultra/icons/cabin.svg',
                    'chalet'      => get_template_directory_uri() . '/assets/ultra/icons/chalet.svg',
                    'clinic'      => get_template_directory_uri() . '/assets/ultra/icons/clinic.svg',
                    'duplex'      => get_template_directory_uri() . '/assets/ultra/icons/duplex.svg',
                    'office'      => get_template_directory_uri() . '/assets/ultra/icons/office.svg',
                    'penthouse'   => get_template_directory_uri() . '/assets/ultra/icons/penthouse.svg',
                    'retail'      => get_template_directory_uri() . '/assets/ultra/icons/penthouse.svg',
                    'studio'      => get_template_directory_uri() . '/assets/ultra/icons/studio.svg',
                    'townhouse'   => get_template_directory_uri() . '/assets/ultra/icons/townhouse.svg',
                    'twinhouse'   => get_template_directory_uri() . '/assets/ultra/icons/twinhouse.svg',
                    'villa'       => get_template_directory_uri() . '/assets/ultra/icons/villa.svg',
                ];

                // Initialize term counts
                $term_counts = [];
                // Loop through properties to count occurrences of each term
                foreach ( $property_ids as $property_id ) {
                    $property_terms = wp_get_post_terms( $property_id, 'property-type' );
                    if ( ! is_wp_error( $property_terms ) ) {
                        foreach ( $property_terms as $property_term ) {
                            $term_id = $property_term->term_id;
                            if ( isset( $term_counts[ $term_id ] ) ) {
                                $term_counts[ $term_id ]++;
                            } else {
                                $term_counts[ $term_id ] = 1;
                            }
                        }
                    }
                }

                $terms_output = [];
                foreach ( $terms as $term ) {
                    $svg_path    = isset( $property_type_svgs[ $term->slug ] ) ? $property_type_svgs[ $term->slug ] : null;
                    $svg_content = $svg_path && file_exists( str_replace( get_template_directory_uri(), get_template_directory(), $svg_path ) )
                        ? file_get_contents( str_replace( get_template_directory_uri(), get_template_directory(), $svg_path ) )
                        : '';
                    $term_count  = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                    $terms_output[] = sprintf(
                        '<span class="property-type-icon">%s %s (%d)</span>',
                        $svg_content,
                        esc_html( $term->name ),
                        $term_count
                    );
                }
                $output = '<p class="property-types">' . implode( '', $terms_output ) . '</p>';
            } else {
                $output = '<p class="property-types">' . esc_html__( 'No property types available.', 'real-estate-sliders' ) . '</p>';
            }
        } else {
            $output = '<p class="property-types">' . esc_html__( 'No properties in this compound.', 'real-estate-sliders' ) . '</p>';
        }

        wp_reset_postdata();
        $GLOBALS['post'] = $original_post;

        return $output;
    }

    /**
     * Helper: Get Property Prices
     */
    private function get_property_prices( $compound_id ) {
        $original_post = $GLOBALS['post'];

        // Initialize min/max
        $min_price = PHP_INT_MAX;
        $max_price = PHP_INT_MIN;

        // Query properties in this compound
        $compound_properties = new WP_Query([
            'post_type'      => 'property',
            'meta_query'     => [
                [
                    'key'     => 'REAL_HOMES_property_compound',
                    'value'   => $compound_id,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => -1,
            'suppress_filters' => false, // WPML
        ]);

        if ( $compound_properties->have_posts() ) {
            while ( $compound_properties->have_posts() ) {
                $compound_properties->the_post();

                $property_price = get_post_meta( get_the_ID(), 'REAL_HOMES_property_price', true );
                if ( is_numeric( $property_price ) ) {
                    $min_price = min( $min_price, (float) $property_price );
                    $max_price = max( $max_price, (float) $property_price );
                }
            }
            wp_reset_postdata();
        }

        $GLOBALS['post'] = $original_post;

        if ( $min_price === PHP_INT_MAX ) {
            $min_price = null;
        }
        if ( $max_price === PHP_INT_MIN ) {
            $max_price = null;
        }

        // Example display logic
        if ( ! is_null( $min_price ) && ! is_null( $max_price ) ) {
            // If you want a range: return sprintf( __( '%1$s - %2$s EGP', 'real-estate-sliders' ), number_format_i18n( $min_price ), number_format_i18n( $max_price ) );
            return sprintf(
                esc_html__( '%s EGP', 'real-estate-sliders' ),
                esc_html( number_format_i18n( $min_price ) )
            );
        } elseif ( ! is_null( $min_price ) ) {
            return sprintf(
                esc_html__( 'Starting from %s EGP', 'real-estate-sliders' ),
                esc_html( number_format_i18n( $min_price ) )
            );
        } else {
            return esc_html__( 'Price on request', 'real-estate-sliders' );
        }
    }

}

// Instantiate the plugin
new Real_Estate_Sliders_Plugin();
