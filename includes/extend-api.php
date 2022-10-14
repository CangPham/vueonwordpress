<?php
/**
 * Functions to extend the WP Rest API
 *
 * @package WordPress
 */

/**
 * Register custom fields for GET requests.
 *
 * Ref: https://developer.wordpress.org/reference/functions/register_rest_field/
 */
function vuetwentyseventeen_extend_api_response() {

	register_rest_field(
		array( 'post' ), // post types.
		'vue_meta', // name of the new key.
		array(
			'get_callback' => 'vue_get_post_meta_fields',
			'update_callback' => null,
			'schema' => null,
		)
	);
}

/**
 * GET callback for the "vue_meta" field defined above.
 *
 * @param array $post_object Details of the current post.
 * @param string $field_name Field Name set in register_rest_field().
 * @param WP_REST_Request $request Current request.
 *
 * @return mixed
 */
function vue_get_post_meta_fields( $post_object, $field_name, $request ) {

	// make additional fields available in the response using an associative array.
	$additional_post_data = array();
	$terms = array();
	$term_links = array();

	$post_id = $post_object['id']; // get the post id.
	foreach ( $post_object['categories'] as $category_id ) {
		$term_data = get_category( $category_id );
		$term_name = $term_data->category_nicename;
		$term_url = get_term_link( $term_data->name, $term_data->taxonomy );
		$term_link = "<a href=\"$term_url\">$term_name</a>";

		array_push( $terms, $term_name );
		array_push( $term_links, $term_link );
	}

	// add categories, custom excerpt, featured image to the api response.
	$img_id  = get_post_thumbnail_id( $post_id );
	$img_alt = get_post_meta( $img_id,'_wp_attachment_image_alt', true );
	$additional_post_data = array(
		'custom_excerpt' => wp_trim_words(
			$post_object['excerpt']['rendered'],
			25,
			' &hellip;'
		),
		'terms' => $terms,
		'term_links' => $term_links,
		'featuredmedia_alt' => get_post_meta(
			$img_id,
			'_wp_attachment_image_alt',
			true
		),
		'featuredmedia_url' => get_the_post_thumbnail_url(
			$post_id,
			'full'
		),
	);

	// return data to the get_callback.
	// this data will be made available in the key "vue_meta".
	return $additional_post_data;
}

add_action( 'rest_api_init', 'register_api_route' );
function register_api_route() {
  
      register_rest_route( 'bmi/v1', 'all', array(
        'methods' => 'GET',
        'callback' => 'handle_get_all',
        )
      );
	  
      register_rest_route( 'bmi/v1', 'add', array(
        'methods' => 'POST',
        'callback' => 'handle_post',
        )
      );     
}
  
  function handle_get_all( $data ) {
	  global $wpdb;
	  $query = "SELECT * FROM `persondata`";
	  $list = $wpdb->get_results($query);
	  return $list;
  }

  
function handle_post1( WP_REST_Request $request ) {
    global $wpdb;
    $item = $request->get_json_params();

    $fields = array();
    $values = array();
    foreach($item as $key => $val) {
        array_push($fields, preg_replace("/[^A-Za-z0-9]/", '', $key));
        array_push($values, $wpdb->prepare('%s', $val));
    }
    $fields = implode(", ", $fields);
    $values = implode(", ", $values);
    $query = "INSERT INTO `userdata` ($fields) VALUES ($values)";
    $list = $wpdb->get_results($query);

    return $list;
}

function handle_post( $request_data ) {
  global $wpdb;
  $data = array();
  $table        = 'userdata';

  // Fetching values from API
  $parameters = $request_data->get_params();
  $name = $parameters['name'];
  $email = $parameters['email'];
  $phone = $parameters['phone'];
  $age = $parameters['age'];
  $gender = $parameters['gender'];
  $height = $parameters['height'];
  $weight = $parameters['weight'];
  $y_weight = $parameters['y_weight'];
  $y_height = $parameters['y_height'];
  $bmi = $parameters['bmi'];
  $datecreated = date("Y/m/d");
	
  $insert = $wpdb->insert($table, array(
		"gender" => $gender,
		"age" => $age,
		"weight" => $weight,
		"height" => $height,
		"y_weight" => $y_weight,
		"y_height" => $y_height,
		"phone" => $phone,
        "email" => $email ,
		"datecreated" => $datecreated ,
		"bmi" => $bmi ,
        ));
  return ($insert);
}