<?php
/*
Plugin Name: Slug Search
Description: Slug Search by post type on admin page
Author: Andrew Polnikov
Version: 1.0.0
*/

add_filter( 'posts_search', 'slug_search_posts', 99, 2);
add_filter( 'posts_search', 'slug_search_menu', 100, 2);
add_filter('acf/fields/post_object/query', 'acfSearchPosts', 100);
add_filter('acf/fields/relationship/query', 'acfSearchPosts', 100);
add_filter('acf/fields/page_link/query', 'acfSearchPosts', 100);
add_filter( 'posts_where', 'slug_posts_for_custom_post_page', 1000 );

function slug_search_menu ( $search, \WP_Query $q ) {
    global $wpdb;

    if (!empty($_POST['action']) && $_POST['action'] == 'menu-quick-search') {
        $s = $_POST['q'];

        if( 'slug:' === mb_substr( trim( $s ), 0, 5 ) )
        {
            $search = $wpdb->prepare(
                " AND {$wpdb->posts}.post_name LIKE %s ",
                str_replace(
                    [ '**', '*' ],
                    [ '*',  '%' ],
                    mb_strtolower(
                        $wpdb->esc_like(
                            trim( mb_substr( $s, 5 ) )
                        )
                    )
                )
            );

            $q->set('orderby', 'post_name' );
            $q->set('order', 'ASC' );
        }
    }

    return $search;
}

function slug_search_posts ( $search, \WP_Query $q )
{
    global $wpdb;

    if(
        ! did_action( 'load-edit.php' )
        || ! is_admin()
        || ! $q->is_search()
        || ! $q->is_main_query()
    )
        return $search;

    $s = $q->get( 's' );

    $basicPostTypes = ['page', 'post'];

    if (in_array($_GET['post_type'], $basicPostTypes)) {
        if( 'slug:' === mb_substr( trim( $s ), 0, 5 ) )
        {
            $search = $wpdb->prepare(
                " AND {$wpdb->posts}.post_name LIKE %s ",
                str_replace(
                    [ '**', '*' ],
                    [ '*',  '%' ],
                    mb_strtolower(
                        $wpdb->esc_like(
                            trim( mb_substr( $s, 5 ) )
                        )
                    )
                )
            );

            $q->set('orderby', 'post_name' );
            $q->set('order', 'ASC' );
        }
    }

    return $search;
}

function acfSearchPosts($args) {

    $s = $args['s'];

    if( 'slug:' === mb_substr( trim( $s ), 0, 5 ) )
    {
        $postName = trim( mb_substr( $s, 5 ) );
        $args['name'] = $postName;

        unset($args['s']);
    }

    return $args;
}

function slug_posts_for_custom_post_page( $search ) {
    global $wpdb;

    if(
        ! did_action( 'load-edit.php' )
        || ! is_admin()
    )
        return $search;

    $basicPostTypes = ['page', 'post'];

    if (!in_array($_GET['post_type'], $basicPostTypes)) {

        if( 'slug:' === mb_substr( trim( $_GET['s'] ), 0, 5 ) )
        {
            $search = $wpdb->prepare(
                " AND {$wpdb->posts}.post_name LIKE %s ",
                str_replace(
                    [ '**', '*' ],
                    [ '*',  '%' ],
                    mb_strtolower(
                        $wpdb->esc_like(
                            trim( mb_substr( trim( $_GET['s'] ), 5 ) )
                        )
                    )
                )
            );
        }
    }

    return $search;
}
