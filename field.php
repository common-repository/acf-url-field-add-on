<?php
if ( class_exists( 'acf_field' ) ){

  class acf_url_object extends acf_field
  {

    function __construct()
    {
      // vars
      $this->name = 'url_object';
      $this->label = __( "URL Object", 'acf-url' );
      $this->category = __( "Choice", 'acf' );
      parent::__construct();
    }


    /*--------------------------------------------------------------------------------------
    *
    *	create_field
    *
    *
    *-------------------------------------------------------------------------------------*/

    function create_field( $field )
    {
      // vars
      $args = array(
        'numberposts' => -1,
        'post_type'   => null,
        'orderby'     => 'title',
        'order'       => 'ASC',
        'post_status' => array( 'publish', 'private', 'draft', 'inherit', 'future' ),
        'suppress_filters' => false,
      );

      $defaults = array(
        'multiple'		=>	0,
        'post_type' 	=>	false,
        'taxonomy' 		=>	false,
        'allow_null'	=>	0,
      );


      $field = array_merge( $defaults, $field );


      // validate taxonomy
      if( ! is_array( $field['taxonomy'] ) )
      {
        $field['taxonomy'] = false;
      }

      if( is_array( $field['taxonomy'] ) && in_array( 'none', $field['taxonomy'] ) )
      {
        $field['taxonomy'] = false;
      }


      // load all post types by default
      if( ! $field['post_type'] || ! is_array( $field['post_type'] ) || $field['post_type'][0] == "" )
      {
        $field['post_type'] = apply_filters( 'acf/get_post_types', array() );
      }

      // Change Field into a select
      $field['type'] = 'select';
      $field['choices'] = array();
      $field['optgroup'] = false;

      foreach( $field['post_type'] as $post_type )
      {
        // set post_type
        $args['post_type'] = $post_type;


        // set order
        if( is_post_type_hierarchical( $post_type ) && ! isset( $args['tax_query'] ) )
        {
          $args['sort_column'] = 'menu_order, post_title';
          $args['sort_order'] = 'ASC';

          $posts = get_pages( $args );
        }
        else
        {
          $posts = get_posts( $args );
        }


        if( $posts )
        {
          foreach( $posts as $post )
          {
            // find title. Could use get_the_title, but that uses get_post(), so I think this uses less Memory
            $title = '';
            $ancestors = get_ancestors( $post->ID, $post->post_type );
            if( $ancestors )
            {
              foreach( $ancestors as $a )
              {
                $title .= '–';
              }
            }
            $title .= ' ' . apply_filters( 'the_title', $post->post_title, $post->ID );


            // status
            if( $post->post_status != "publish" )
            {
              $title .= " ($post->post_status)";
            }

            // WPML
            if( defined('ICL_LANGUAGE_CODE') )
            {
              $title .= ' (' . ICL_LANGUAGE_CODE . ')';
            }

            // add to choices
            if( count( $field['post_type'] ) == 1 && $field['taxonomy'] == false )
            {
              $field['choices'][ 'post-' . $post->post_type . '-' . $post->ID ] = $title;
            }
            else
            {
              // group by post type
              $post_type_object = get_post_type_object( $post->post_type );
              $post_type_name = $post_type_object->labels->name;

              $field['choices'][$post_type_name][ 'post-' . $post->post_type . '-' . $post->ID] = $title;
              $field['optgroup'] = true;
            }


          }
          // foreach( $posts as $post )
        }
        // if($posts)
      }

      //add tax link
      foreach( $field['taxonomy'] as $taxonomy )
      {
        if ( taxonomy_exists( $taxonomy ) ){
          $tax_object = get_taxonomy( $taxonomy );
          $tax_name = $tax_object->labels->name;

          $terms = get_terms($taxonomy, array('get' => 'all', 'orderby' => 'id'));
          foreach ( $terms as $term ){
            $field['choices'][$tax_name]['tax-' . $taxonomy . '-' . $term->term_id] = $term->name;
          }
        }
      }
      // foreach( $field['post_type'] as $post_type )

      //wp_enqueue_script('acf_url_field',plugin_dir_url(__FILE__).'js/acf_url.js');

      if( is_array( $field['value'] ) && isset( $field['value']['link'] ) ) {
        $value = $field['value']['link'];
        $label = $field['value']['label'];
        $field['value'] = $value;
      } else {
        $value = $field['value'];
        $label = "";
      }
      $internal= ( preg_match( '!^(post|tax)-(.*?)-([0-9]+)$!', $field['value'] ) && strpos( $field['value'], "http" ) == false );
      ?>
      <div class="acf_url_field_block">
        <input type="hidden" name="<?php echo $field['name'];?>" value="" class="acf_url_true_value">
        <table class="widefat">
          <tbody>
          <tr>
            <td class="label"><label><?php echo __( "Label", 'acf-url');?></label></td>
            <td>
              <?php
              $b =  preg_match_all( "#(\[.*?\])#", $field['name'], $matches );
              $newfieldname = "";
              $glue = "";
              $hierarchy_field = array();
              foreach ( $matches[1] as $key => $fieldname ) {
                if ( strpos( $fieldname, 'field_' ) ) {
                  $fieldname = trim( $fieldname, '[]' );
                  $fieldinfo = $this->get_acf_field( $fieldname );
                  if ( $fieldinfo && isset( $fieldinfo['name'] ) ) {
                    $hierarchy_field[] = $fieldname;
                    $newfieldname .= $glue . $fieldinfo['name'];
                  } elseif ( ! empty( $hierarchy_field ) ) {
                    $parentname = end( $hierarchy_field );
                    $currentfieldinfo = $this->get_acf_field( $parentname );
                    $name = $currentfieldinfo['sub_fields'][$fieldname]['name'];
                    $newfieldname .= $glue.$name;
                  }
                  $glue = "_";
                } else {
                  $fieldname = trim( $fieldname, '[]' );
                  $newfieldname .= $glue . $fieldname;
                  $glue = "_";
                }
              }
              ?>
              <input type="text" class="acf_url_label" value="<?php echo $label;?>" />
            </td>
          </tr>
          <tr class="acf_url_field_block">
            <td class="label">
              <label for=""><?php echo __("URL", "acf-url");?></label>
              <input type="hidden" name="<?php echo $field['name'];?>" value="" class="acf_url_true_value">
            </td>
            <td>
              <ul class="acf_url_field_choice radio_list radio horizontal">
                <li><label><input type="radio" name="choice_<?php echo $field['id'];?>[]" value="1" <?php if( $internal ): ?>checked="checked"<?php endif;?>><?php echo __( "Internal", 'acf-url');?></label></li>
                <li><label><input type="radio" name="choice_<?php echo $field['id'];?>[]" value="0" <?php if( ! $internal ): ?>checked="checked"<?php endif;?>><?php echo __( "External", 'acf-url');?></label></li>
              </ul>
              <div class="acf_url_field_internal">
                <?php
                // create field
                do_action( 'acf/create_field', $field );
                ?>
              </div>
              <div class="acf_url_field_external">
                <input type="text" value="<?php echo ( ( $internal ) ? '' : $value );?>" id="text-<?php echo $field['id'];?>" class="<?php echo $field['class']; ?>" />
                <span><?php echo __('Please specify the http://', "acf-url" ); ?></span>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

    <?php

    }


    /*--------------------------------------------------------------------------------------
    *
    *	create_options
    *
    *
    *-------------------------------------------------------------------------------------*/

    function create_options( $field )
    {
      $key = $field['name'];

      // defaults
      $defaults = array(
        'post_type' 	=>	'',
        'multiple'		=>	0,
        'allow_null'	=>	0,
        'taxonomy' 		=>	array( 'all' ),
      );

      $field = array_merge( $defaults, $field );

      ?>
      <tr class="field_option field_option_<?php echo $this->name; ?>">
        <td class="label">
          <label for=""><?php _e("Post Type",'acf-url'); ?> <?php _e( "(Internal link)", 'acf-url' ); ?></label>
        </td>
        <td>
          <?php
          $choices = array(
            ''	=>	__( "All", 'acf' )
          );
          $choices = apply_filters( 'acf/get_post_types', $choices );

          do_action( 'acf/create_field', array(
            'type'	=>	'select',
            'name'	=>	'fields['.$key.'][post_type]',
            'value'	=>	$field['post_type'],
            'choices'	=>	$choices,
            'multiple'	=>	1,
          ));

          ?>
        </td>
      </tr>
      <tr class="field_option field_option_<?php echo $this->name; ?>">
        <td class="label">
          <label><?php _e( "Taxonomy", 'acf' ); ?> <?php _e( "(Internal link)", 'acf-url' ); ?></label>
        </td>
        <td>
          <?php
          $choices = array(
            'none' => __( "None", 'acf' )
          );
          //add taxonomie link
          $taxonomies = get_taxonomies( array(), 'objects' );
          $ignore = array( 'post_format', 'nav_menu', 'link_category' );

          foreach( $taxonomies as $taxonomy )
          {
            if( in_array($taxonomy->name, $ignore) )
            {
              continue;
            }
            $choices[ $taxonomy->name ] = $taxonomy->name;
          }


          do_action( 'acf/create_field', array(
            'type'	=>	'select',
            'name'	=>	'fields['.$key.'][taxonomy]',
            'value'	=>	$field['taxonomy'],
            'choices' => $choices,
            'multiple'	=>	1,
          ) );

          ?>
        </td>
      </tr>
      <tr class="field_option field_option_<?php echo $this->name; ?>">
        <td class="label">
          <label><?php _e( "Allow Null?", 'acf' ); ?></label>
        </td>
        <td>
          <?php

          do_action( 'acf/create_field', array(
            'type'	=>	'radio',
            'name'	=>	'fields['.$key.'][allow_null]',
            'value'	=>	$field['allow_null'],
            'choices'	=>	array(
              1	=>	__( "Yes", 'acf' ),
              0	=>	__( "No", 'acf' ),
            ),
            'layout'	=>	'horizontal',
          ));

          ?>
        </td>
      </tr>
    <?php
    }


    /*--------------------------------------------------------------------------------------
    *
    *	format_value_for_api
    *
    *
    *-------------------------------------------------------------------------------------*/

    function format_value_for_api( $value, $post_id, $field )
    {
      // get value
      //$value = parent::get_value( $post_id, $field );

      // no value?
      if( ! $value )
      {
        return false;
      }


      // null?
      if( $value == 'null' )
      {
        return false;
      }


      // external / internal
      if( is_array( $value ) && isset( $value['link'] ) ) {
        $link = $value['link'];
        $internal= ( preg_match( '!^(post|tax)-(.*?)-([0-9]+)$!', $link, $matches ) && strpos( $link, "http" ) == false );
        if( $internal && $matches ) {
          $type = $matches[1];
          $object = $matches[2];
          $id = $matches[3];
          if ( $type == 'post' ){
            $post = get_post( intval($id) );
            if  ( isset($post) && $post->ID > 0 ){
              $url = get_permalink( $post->ID );
              $title = $post->post_title;
            }else {
              $url = '';
              $title = '';
            }
            $value['link']= $url;
          } else if ( $type == 'tax' ) {
            $term = get_term( intval($id), $object );
            if  ( isset($term) && $term->term_id > 0 ){
              $url = get_term_link( $term->term_id, $object );
              $title = $term->name;
            } else {
              $url = '';
              $title = '';
            }
            $value['link']= $url;
          }
          if( empty( $value['label'] ) && $title ) {
            $value['label'] = $title;
          }
        }
      }


      // return the value
      return $value;
    }

    /*--------------------------------------------------------------------------------------
    *
    *	update_value
    *
    *	@author Elliot Condon
    *	@since 2.2.0
    *
    *-------------------------------------------------------------------------------------*/

    function update_value( $value, $post_id, $field ) {
      $object = stripslashes( $value );
      $object = json_decode( $object );
      if( isset( $object->link ) ) {
        $value = (array)$object;
      }
      return $value;
    }

    //************** get field name by key ***********
    function get_acf_field( $fieldkey )
    {
      // vars
      global $wpdb;


      // get field from postmeta
      $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $fieldkey ) );

      if( $result )
      {
        $result = maybe_unserialize( $result );
        return $result;
      }


      // return
      return false;

    }

  }
  new acf_url_object();
}

