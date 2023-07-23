<?php

global $post;
global $wpdb;

//function to check if user logged in && his role

if (is_user_logged_in()) {
    $user = wp_get_current_user();
       $role = (array) $user->roles;
       if($role[0] == 'customer' ){
   ?>
       <script>
  
       jQuery(document).ready(function($) {
       });
   
       </script>		
    <?php 
       } 
}
//if is admin

if(is_super_admin()){
	
	
	?>
	<script>
		jQuery(document).ready(function($) {
			
			
		
		});	
</script>
	<?php
}

//cancel subscription if user order new one to avoid having two susbscriptions same time

add_action( 'woocommerce_thankyou', function ($order_id) {
    $args = array(
    'subscriptions_per_page' => -1,
    'customer_id'            => get_current_user_id(),
);

$subscriptions = wcs_get_subscriptions($args);

foreach ($subscriptions as $subscription) {
    $s_order_id = method_exists( $subscription, 'get_parent_id' ) ? $subscription->get_parent_id() : $subscription->order->id;
    if ($s_order_id != $order_id) {
        $cancel_note = 'Customer purchased new subscription in order #' . $order_id;
        $subscription->update_status( 'cancelled', $cancel_note );
    }
}
},  10, 1  );

//show hide admin bar

add_action('after_setup_theme', function(){
    if (!current_user_can('administrator') && !is_admin()) {
   show_admin_bar(false);
 }
});

//affect seconde role two all users

add_action( 'admin_init', function() {
	$users = get_users();
    $second_role = 'Author';
    
    foreach ( $users as $user ) {
        $user->add_role( $second_role );
    }
} );

// shortcode that return a break

add_shortcode( 'br', function() {
    return '<br />';
});

//function to prevent users to add more than two posts per month 

add_action('wp_insert_post_data', function($data, $postarr){
  
    $user_id = $postarr['post_author'];
    $post_type = $postarr['post_type'];
    $post_status = $postarr['post_status'];

    if ( !user_can($user_id, 'administrator') && $post_type === 'post' && $post_status === 'publish') {
        $current_month = date('m');

        $args = array(
            'author'         => $user_id,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'date_query'     => array(
                'year'  => date('Y'),
                'month' => $current_month,
            ),
        );
        $user_posts = get_posts($args);
        $post_count = count($user_posts);

        if ($post_count >= 2) {
            $data['post_status'] = 'draft';
            wp_die('Vous avez atteint la limite maximale d\'articles publiés pour ce mois. Le nouvel article est passé à l\'état de "brouillon".');
        }
    }

    return $data;
    
}, 10, 2);


//allows products to be purchasable for users with specefic roles 

add_filter('woocommerce_is_purchasable', function($purchasable, $product){
    $allowed_roles = array('administrator', 'customer_alt');

    if (is_user_logged_in() && array_intersect($allowed_roles, wp_get_current_user()->roles)) {
        return $purchasable;
    } else {
        return false;
    }
	
}, 10, 2);

//shorcode dynamic

add_shortcode('webinaire', function(){
    $current_user = wp_get_current_user();
    $author_id = $current_user->ID;
    $shortcode = '[vg_display_admin_page page_url="https://franchisescanner.com/wp-admin/edit.php?post_type=wpstream_product&author=' . $author_id . '&mode=list"]';
    return do_shortcode($shortcode);
    });


//method to perform a database query and retrieve a single value from the database

$wpdb->get_var("query");

//jquery function to creach a back button

jQuery('#btn-back').on('click', function() {
    window.history.go(-1); 
    return false;
  });

  //get active subscription , and work on every procut using id by users
  $user_id = get_current_user_id();

  $subsc = wcs_get_subscriptions([
      "customer_id" => $user_id,
      "subscription_status" => "active",
      "subscriptions_per_page" => -1,
  ]);

  if (empty($subsc)) {
    //
  }else{

    foreach ($subsc as $subscription) {
        $subscription_id = $subscription->get_id();

        $order = new WC_Order($subscription_id);

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
                if ($product_id == 27696) {
                }
            }
    }
  }

  //unserialized Data 

  $results = $wpdb->get_results("SELECT * FROM `mgc_db7_forms`");
  foreach( $results as $result ) 
  {

          $unserializedData = unserialize($result->form_value);
          //$unserializedData['servicesproposes'];
  }

//current url

$current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

//get post meta

$listing_type = get_post_meta( $post->ID, '_case27_listing_type' );

//export data csv file 

global $wpdb;


$results = $wpdb->get_results("SELECT username, pagetitle,page_url, date, COUNT(*) as 'number Click' FROM wor8313_click_per_user GROUP BY username, page_url", ARRAY_A);
 $filename = "Nombre_de_cliques_par_jour_et_par_PDV" . date("Y-m-d_H-i", time()) . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=" . $filename);


$output = fopen('php://output', 'w');
fputcsv($output, array('PDV', 'Titre de page','URL de page','Date Click','Nombre cliques'));
foreach ($results as $row) {
  fputcsv($output, $row);
}
fclose($output);