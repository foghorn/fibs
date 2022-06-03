<?php
  /*
  Plugin Name: Featured Image Bulk Set
  Plugin URI: https://github.com/foghorn/fibs
  description: A plugin to set the featured image for posts where none exists using the first image in the post
  Version: 1.5.4
  Author: Nick Leghorn
  Author URI: https://blog.nickleghorn.com
  License: GPL2
  */

  //Validate image and insert
  function fibs_CheckAndPost($ID,$image,$real)
  {
    $ID = sanitize_text_field($ID);
    $image = sanitize_text_field($image);
    $real = sanitize_text_field($real);
    $return = "";

    //Check that the two posts passed are a number
    if ((is_numeric($image)) AND  (is_numeric($ID)))
    {
      //Make sure we are setting for a post
      if (get_post_status($ID) != FALSE)
      {
        //Check that this number is actually a Post ID of an image attachment
        if (wp_attachment_is_image($image))
        {
          //Check that the end user really wants to do this
          if ($real == 1)
          {
            set_post_thumbnail($ID,$image);
          }
          //Otherwise, tell them what would have happened
          else
          {
            $return = $return . "Would have set image " . wp_kses($image,array()) . "<br>";
          }
        }
        else
        {
          $return = $return . "ERROR: Identified image ID is not an image<br>";
        }
      }
      else
      {
        $return = $return . "ERROR: Tried to set the featured image for something that is not a post<br>";
      }
    }
    else
    {
      $return = $return . "ERROR: Either the Post ID or the Featured Image ID are not a number<br>";
    }

    return $return;
  }

  //Check whether a post has a featured image set, and if none set, find and set a suitable image
  function fibs_featured_image_set($Return_ID,$tablename,$con,$dim,$firstlast,$forreal,$override)
  {
    $return = "";

    //Check that there is a featured image
    if (get_post_thumbnail_id($Return_ID) == FALSE)
    {
      $return = $return . "NO FEATURED IMAGE!<br>";
      
      //Find featured image
      $img_ref = '';
      $img_slice = '';
      
      //Grab the post content
      $E = mysqli_query($con,"SELECT post_content FROM " . $tablename . " WHERE ID = '" . $Return_ID . "'");
      $F = mysqli_fetch_array($E);

      //Sanitize content
      $return_content = wp_kses_post($F['post_content']);

      if (strlen($return_content) > 0)
      {
        $return = $return . "Grabbed post: " . wp_kses(md5($return_content),array()) . "<br>";
        
        //Check override
        if ( (strlen($dim) > 0) AND ($override == 1) )
        {
          //Check that this is really an image and post
          $return = $return . fibs_CheckAndPost($Return_ID,$dim,$forreal);
        }
        //is there an image to be found?
        elseif (substr_count($return_content,'wp-image-'))
        {
          $return = $return . "Image found!<br>";
          
          //First or last image?
          if ($firstlast == 0)
          {
            //Identify the first wp-image- referenced
            $img_ref = stripos($return_content,'wp-image-');

            $img_slice = substr($return_content,$img_ref);

            //Find where this string ends
            $counter = 9;

            while (preg_match('/^[a-zA-Z0-9\-]$/',substr($img_slice,$counter,1)))
            {
              $counter++;
            }

            //Slice string to just post ID
            $thumbnailID = substr($img_slice,9,($counter - 9));

            $return = $return . fibs_CheckAndPost($Return_ID,$thumbnailID,$forreal);
            
          }
          else
          {
            //Identify the last wp-image- referenced
            $img_ref = strripos($return_content,'wp-image-');

            $img_slice = substr($return_content,$img_ref);

            //Find where this string ends
            $counter = 9;

            while (preg_match('/^[a-zA-Z0-9\-]$/',substr($img_slice,$counter,1)))
            {
              $counter++;
            }

            //Slice string to just post ID
            $thumbnailID = substr($img_slice,9,($counter - 9));

            $return = $return . fibs_CheckAndPost($Return_ID,$thumbnailID,$forreal);
          }

        }
        elseif (strlen($dim) > 0)
        {
          //Check that this is really an image and post
          $return = $return . fibs_CheckAndPost($Return_ID,$dim,$forreal);
                        
        }
        else
        {
          $return = $return . "ERROR: No image found<br>";
        }

      }
      else
      {
        $return = $return . "ERROR: Zero length post<br>";
      }
      
    }
    else
    {
      $return = $return . "FEATURED IMAGE SET!<br>";
    }

    return $return;
  }

  //Automated check if featured image is set for each post
  function fibs_auto_featured_image()
  {
    //Make sure we actually want it to run
    if (get_option('fibs_automated') == 1)
    {
      //Check that we are on a post that is published
      if ((get_post_type() == 'post') AND is_singular() AND ('publish' === get_post_status()))
      {
        
        //check whether there is a featured image
        if (get_post_thumbnail_id(get_the_ID()) ==  FALSE)
        {
          //Get and sanitize table name
          global $wpdb;
          $prefix = $wpdb->prefix;
          $tablename = sanitize_text_field($prefix  . "posts");

          $con=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          
          //Grab options from the database
          $dim = sanitize_text_field(get_option('fibs_dim'));

          if ($dim != '')
          {
            if (wp_attachment_is_image($dim))
            {
              $dim = $dim;
            }
            else
              $dim = '';
          }
          else
            $dim = '';

          $override = sanitize_text_field(get_option('fibs_override'));

          if ($override != 1)
            $override = 0;

          $forreal = 1;

          fibs_featured_image_set(get_the_ID(),$tablename,$con,$dim,$firstlast,$forreal,$override);
        }      
      }
    }
  }
  add_action( 'wp', 'fibs_auto_featured_image' );

  function fibs_checked_checker($optionkey,$value)
  {
    if (get_option($optionkey) == FALSE)
    {
      if ($value == 0)
        return "checked=\"checked\"";
    }
    else
    {
      $check = sanitize_text_field(get_option($optionkey));

      if ($check == $value)
        return "checked=\"checked\"";
      else
        return "";
    }
    
  }


  //Add settings page to the Admin menu
  function fibs_add_settings_page() {
  add_options_page( 'Featured Image Bulk Set', 'FIBS Menu', 'manage_options', 'fibs_plugin', 'fibs_render_plugin_settings_page' );
  }
  add_action( 'admin_menu', 'fibs_add_settings_page' );

  function fibs_render_plugin_settings_page() {
    //Sanitize user input
    $execute = sanitize_text_field($_GET['execute']);
    $secretcheck = sanitize_text_field($_POST['secretcheck']);
    $dim = sanitize_text_field($_POST['dim']);
    $override = sanitize_text_field($_POST['override']);
    $forreal = sanitize_text_field($_POST['forreal']);
    $firstlast = sanitize_text_field($_POST['firstlast']);
    $automated = sanitize_text_field($_POST['automated']);
    $drafts = sanitize_text_field($_POST['drafts']);

    //Get and sanitize table name
    global $wpdb;
    $prefix = $wpdb->prefix;
    $tablename = sanitize_text_field($prefix  . "posts");
    ?>
    <h2>Featured Image Bulk Set Functionality</h2>
    <?php

    //Check whether we need to save these values
    if ($forreal == 2)
    {
      //First or last?
      if (is_numeric($firstlast))
        update_option('fibs_firstlast',$firstlast);

      //Default image?
      if ((get_post_status($dim) != FALSE) AND wp_attachment_is_image($dim))
        update_option('fibs_dim',$dim);
      elseif ($dim != '')
        update_option('fibs_dim','');

      //Override?
      if (is_numeric($override) AND (get_option('fibs_dim') != ''))
        update_option('fibs_override',$override);
      else
        update_option('fibs_override',0);

      //Include drafts?
      if (is_numeric($drafts))
        update_option('fibs_drafts',$drafts);

      //Do it automatically?
      if (is_numeric($automated))
        update_option('fibs_automated',$automated);

    }
    
    if (($execute != 1) OR ($forreal == 2))
    {
      ?>
        <form action="options-general.php?page=fibs_plugin&execute=1" method="post">

        <!-- INVISIBLE CHECK -->
        <input type="hidden" id="secretcheck" name="secretcheck" value="1">

        <!-- FIRST OR LAST -->
        <h3>First or Last Image for Default?</h3>
        <input type="radio" id="first" name="firstlast" value="0" <?php echo fibs_checked_checker('fibs_firstlast',0); ?> > First Image<br>
        <input type="radio" id="first" name="firstlast" value="1" <?php echo fibs_checked_checker('fibs_firstlast',1); ?> > Last Image<br>
        <br>

        <!-- DEFAULT IMAGE -->
        <h3>Set a Default Image for Posts Without Images?</h3>
        <input type="text" id="dim" name="dim" value=<?php
        
        $dim = sanitize_text_field(get_option('fibs_dim'));

        if ($dim != FALSE)
          echo "\"" . wp_kses($dim,array()) . "\"";
        
        else
          echo "\"\"";

        ?>><br>
        (Use image ID. Leave blank to set NO image in those cases.)<br>
        <br>
        Override finding images in posts with the default image?<br>
        <input type="radio" id="override" name="override" value="0" <?php echo fibs_checked_checker('fibs_override',0); ?> > NO!<br>
        <input type="radio" id="override" name="override" value="1" <?php echo fibs_checked_checker('fibs_override',1); ?> > Yes!<br>
        NOTE: Setting this option will set this image as the Featured Image for ALL posts without a current Featured Image, even those that contain images.<br>
        I recommend setting a default image WITHOUT the override for use with the automated featured image option.<br>
        <br>

        <!-- DRAFT -->
        <h3>Include Drafts?</h3>
        <input type="radio" id="drafts" name="drafts" value="0" <?php echo fibs_checked_checker('fibs_drafts',0); ?> > Yes!<br>
        <input type="radio" id="drafts" name="drafts" value="1" <?php echo fibs_checked_checker('fibs_drafts',1); ?> > No!<br>
        <br>

        <!-- AUTO -->
        <h3>Enable automatically adding a featured image to all new posts?</h3>
        <input type="radio" id="automated" name="automated" value="0" <?php echo fibs_checked_checker('fibs_automated',0); ?> > NO!<br>
        <input type="radio" id="automated" name="automated" value="1" <?php echo fibs_checked_checker('fibs_automated',1); ?> > Yes!<br>
        <br>

        <!-- TEST OR REAL -->
        <h3>Execute this for all posts in the database right now?</h3>
        <input type="radio" id="forreal" name="forreal" value="0" checked="checked"> NO! Test run first, no changes will be made.<br>
        <input type="radio" id="forreal" name="forreal" value="2"> NO! Only update these option settings.<br>
        <input type="radio" id="forreal" name="forreal" value="1"> Yes!<br>
        
        <br>


        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
      <?php
    }
    elseif ($secretcheck == 1)
    {
      
      //Make sure the default featured image, if set, is actually a usable image
      if ((($dim != NULL) OR ($dim != 0)) AND ((is_numeric($dim) == FALSE) OR (wp_attachment_is_image($dim) == FALSE)))
      {
        echo "ERROR: Entered default featured image is not correct, please check and ensure that you entered the correct Post ID for the required featured image.<br>";
      }
      else
      {
        $con=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($drafts == 1)
        {
          $draftsinclude = " AND post_status = 'publish'";
        }
        else
        {
          $draftsinclude = '';
        }

        $A = mysqli_query($con,"SELECT * FROM " . $tablename . " WHERE post_type = 'post'" . $draftsinclude);
        while($B = mysqli_fetch_array($A))
        {
          //Sanitize database returns
          $Return_ID = sanitize_text_field($B['ID']);
          
          echo "<br>Checking " . wp_kses($Return_ID,array()) . "<br>";

          echo fibs_featured_image_set($Return_ID,$tablename,$con,$dim,$firstlast,$forreal,$override);
          
        }
        echo '<br><br>FINSIHED: <a href="options-general.php?page=fibs_plugin">Back to the beginning!</a>';
      }
          
    }
    else
    {
      echo 'ERROR: <a href="options-general.php?page=fibs_plugin">Please click here to try again</a>';
    }

  }
?>