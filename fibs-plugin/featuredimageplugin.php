<?php
  /*
  Plugin Name: Featured Image Bulk Set
  description: A plugin to set the featured image for posts where none exists using the first image in the post
  Version: 1.0
  Author: Nick Leghorn
  Author URI: https://blog.nickleghorn.com
  License: GPL2
  */


  function fibs_add_settings_page() {
  add_options_page( 'Featured Image Bulk Set', 'FIBS Menu', 'manage_options', 'fibs_plugin', 'fibs_render_plugin_settings_page' );
  }
  add_action( 'admin_menu', 'fibs_add_settings_page' );

  function fibs_render_plugin_settings_page() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $tablename = $prefix  . "posts";
    ?>
    <h2>Featured Image Bulk Set Functionality</h2>
    <?php

    if ($_GET['execute'] != 1)
    {
      ?>
        <form action="options-general.php?page=fibs_plugin&execute=1" method="post">

        <!-- INVISIBLE CHECK -->
        <input type="hidden" id="secretcheck" name="secretcheck" value="1">

        <!-- FIRST OR LAST -->
        First or Last Image for Default?<br>
        <input type="radio" id="first" name="firstlast" value="1" checked="checked"> First Image<br>
        <input type="radio" id="first" name="firstlast" value="0"> Last Image<br>
        <br>

        <!-- DEFAULT IMAGE -->
        <label for="dim">Set a Default Image for Posts Without Images?</label><br>
        <input type="text" id="dim" name="dim"><br>
        Use image ID. Leave blank to set NO image in those cases.<br>
        <br>

        <!-- TEST OR REAL -->
        Do it for real?<br>
        <input type="checkbox" id="forreal" name="forreal" value="1">
        <label for="forreal"> Yes!</label><br>
        <br>


        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
      <?php
    }
    elseif ($_POST['secretcheck'] == 1)
    {

      $con=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $A = mysqli_query($con,"SELECT * FROM " . $tablename . " WHERE post_type = 'post'");
      while($B = mysqli_fetch_array($A))
      {
        echo "<br>Checking " . $B['ID'] . "<br>";

        
        //Check that there is a featured image
        if (get_post_thumbnail_id($B['ID']) == FALSE)
        {
          echo "NO FEATURED IMAGE!<br>";
          
          //Find featured image
          $img_ref = '';
          $img_slice = '';
          
          //Grab the post content
          $E = mysqli_query($con,"SELECT post_content FROM " . $tablename . " WHERE ID = '" . $B['ID'] . "'");
          $F = mysqli_fetch_array($E);

          if (strlen($F['post_content']) > 0)
          {
            echo "Grabbed post: " . md5($F['post_content']) . "<br>";
            
            //is there an image to be found?
            if (substr_count($F['post_content'],'wp-image-'))
            {
              echo "Image found!<br>";
              
              //First or last image?
              if ($_POST['firstlast'] == 1)
              {
                //Identify the first wp-image- referenced
                $img_ref = stripos($F['post_content'],'wp-image-');

                $img_slice = substr($F['post_content'],$img_ref);

                //Find where this string ends
                $counter = 9;

                while (preg_match('/^[a-zA-Z0-9\-]$/',substr($img_slice,$counter,1)))
                {
                  $counter++;
                }

                //Slice string to just post ID
                $thumbnailID = substr($img_slice,9,($counter - 9));

                if ($_POST['forreal'] == 1)
                {
                  set_post_thumbnail($B['ID'],$thumbnailID);
                }
                else
                {
                  echo "Would have set image " . $thumbnailID . "<br>";
                }

                
              }
              else
              {
                //Identify the first wp-image- referenced
                $img_ref = strripos($F['post_content'],'wp-image-');

                $img_slice = substr($F['post_content'],$img_ref);

                //Find where this string ends
                $counter = 9;

                while (preg_match('/^[a-zA-Z0-9\-]$/',substr($img_slice,$counter,1)))
                {
                  $counter++;
                }

                //Slice string to just post ID
                $thumbnailID = substr($img_slice,9,($counter - 9));

                if ($_POST['forreal'] == 1)
                {
                  set_post_thumbnail($B['ID'],$thumbnailID);
                }
                else
                {
                  echo "Would have set image " . $thumbnailID . "<br>";
                }
              }

            }
            else
            {
                echo "ERROR: No image found<br>";
            }

          }
          else
          {
              echo "ERROR: Zero length post<br>";
          }
          
        }
        else
        {
          echo "FEATURED IMAGE SET!<br>";
        }
      }
      echo '<br><br>FINSIHED: <a href="options-general.php?page=fibs_plugin">Back to the beginning!</a>';
          
    }
    else
    {
      echo 'ERROR: <a href="options-general.php?page=fibs_plugin">Please click here to try again</a>';
    }

  }
?>