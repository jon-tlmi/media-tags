<?php
// [media-tags media_tags="alt-views,page-full,thumb" tags_compare="AND" orderby="menu_order" display_item_callback=""]

function mediatags_shortcode_handler($atts, $content=null, $tableid=null) 
{
	global $post, $mediatags;
  
  // because we can't necessarily trust input from the contributor
  $atts = array_map( 'sanitize_text_field', $atts );

  // test stuff
  // echo mediatags_column_header($atts);
  // end of test stuff 
  if ((!isset($atts['return_type'])) || ($atts['return_type'] != "li")){
		$atts['return_type'] = "li";
  }

  if (!isset($atts['before_list'])) {
		$atts['before_list'] = "<ul>";
  }
  
  /** if (!isset($atts['columns'])) {
	*	// default column layout here. suggest icon, filename, author, filesize.
	} **/
  
	if ($atts['display_item_callback'] == "mediatags_mdoctypes") {
	  // insert column bits here
	  $atts['before_list'] = mediatags_column_header($atts);
		$atts['after_list'] = "</tbody>
		</table>
		<script type='text/javascript'>
		runScript();

		function runScript() {
    		// Workaround due to jQuery load issues.
    		if( window.$ ) {
        		// action that depends on jQuery.
				$(document).ready(function() {
					$('#mt_mdoctypes').dataTable();
				} );
    		} else {
        		// wait 50 milliseconds and try again.
       		 window.setTimeout( runScript, 50 );
  		  }
		}
		</script>";
	}

  if (!isset($atts['after_list'])) {
		$atts['after_list'] = "</ul>";
  }

  if ((!isset($atts['display_item_callback'])) || (strlen($atts['display_item_callback']) == 0)) {
		$atts['display_item_callback'] = 'default_item_callback';
  }

  if ((isset($atts['post_parent'])) && ($atts['post_parent'] == "this")) {
		$atts['post_parent'] = $post->ID;
  }
		
	$atts['call_source'] = "shortcode";
		
	//echo "atts<pre>"; print_r($atts); echo "</pre>";
	
	if (!is_object($mediatags)) 
		$mediatags = new MediaTags();
		
	$output = $mediatags->get_attachments_by_media_tags($atts);
	if ($output)
	{
		if (isset($atts['before_list']))
		{
			$output = $atts['before_list'] . $output;
		}
		
		if (isset($atts['after_list']))
		{
			$output = $output .$atts['after_list'];			
		}
	}
   if ($output === NULL) 
   {
	$output = "No linked tag items found.";
	}
	return $output;
}

// This is the default callback function for displaing the media tag items. You can override this by creating your own function under 
// your theme and passing the name of that function as parameter 'display_item_callback'. 
// Your function needs to support the one argument $post_item which is the attachment item itself. 

// In the example (default) function below I use an optional second argument to control the size of the image displayed. The size argument is passed into get_attachments_by_media_tags() to control which image is output. As you can define your own callback function you can obviously control which version of the image you are going to display. 

function default_item_callback($post_item, $size='medium')
{
//	echo "post_item<pre>"; print_r($post_item); echo "</pre>";
	$image_src 	= wp_get_attachment_image_src($post_item->ID, $size);
	
	return '<li class="media-tag-list" id="media-tag-item-'.$post_item->ID.'"><img 
		src="'.$image_src[0].'" width="'.$image_src[1].'" height="'.$image_src[2].'"
			title="'.$post_item->post_title.'" /></li>';
}

function mediatags_item_callback_with_caption($post_item, $size='medium')
{
	$image_src 	= wp_get_attachment_image_src($post_item->ID, $size);
	
	$output_str = '<li class="media-tag-list" id="media-tag-item-'.$post_item->ID.'">';

	// WP stores the Caption into the post_excerpt 
	if (strlen($post_item->post_excerpt))
	{
		$output_str .= '[caption id="attachment_'. $post_item->ID. '" 
			align="alignnone" width="'. $image_src[1] .'" caption="'. $post_item->post_excerpt .'"]';
	}
	$output_str .= '<img src="'.$image_src[0].'" width="'.$image_src[1].'" height="'.$image_src[2].'"
			title="'.$post_item->post_title.'" />';
			
	if (strlen($post_item->post_excerpt))
	{
		$output_str .= '[/caption]';
	}
	return do_shortcode($output_str);
}

function mediatag_item_callback_show_meta($post_item, $size='medium')
{
//	$image_src 	= wp_get_attachment_image_src($post_item->ID, $size);
	$media_meta = wp_get_attachment_metadata($post_item->ID);
//	echo "media_meta<pre>"; print_r($media_meta); echo "</pre>";
	
	$image_meta = $media_meta['image_meta'];

	//print_r ($metadata);
	$meta_out = '';
	if($image_meta['camera']) 				$meta_out .= $image_meta['camera'].' ';
	if($image_meta['focal_length']) 		$meta_out .= '@ '. $image_meta['focal_length'] .' mm ';
	if($image_meta['shutter_speed']) 		$meta_out .= '- ¹/'. (1/($image_meta['shutter_speed'])) .' sec';
	if($image_meta['aperture']) 			$meta_out .= ', ƒ/'.$image_meta['aperture'] .' ';
	if($image_meta['iso']) 					$meta_out .= ', ISO '.$image_meta['iso'] .' ';
	if($image_meta['created_timestamp']) 	$meta_out .= ' on '.date('j F, Y', $image_meta['created_timestamp']) .' ';

	return $meta_out;
}
function mediatags_get_icon_for_attachment($post_id) {
  // $base = __FILE__ . "img/";
  $plugin = "Media Tags";
  $base = plugin_dir_url( $plugin ) . "media-tags/img/";
  $type = get_post_mime_type($post_id);
  switch ($type) {
    case 'image/jpeg':
    	return $base . "file_jpg.png"; break;
    case 'image/png':
    	return $base . "file_png.png"; break;
    case 'image/gif':
    	return $base . "file_gif.png"; break;
    case 'audio/mpeg':
    	return $base . "file_mp3.png"; break;
    case 'application/pdf': 
    	return $base . "file_pdf.png"; break;
    case 'application/msword':
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
    	return $base . "file_doc.png"; break;
    case 'application/vnd.ms-powerpoint':
    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
    	return $base . "file_ppt.png"; break;
    case 'application/vnd.ms-excel':
    case 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
    	return $base . "file_xls"; break;
    case 'text/html':
      return $base . "file_html.png"; break;
    case 'text/xml':
      return $base . "file_xml.png"; break;
    default:
	return $base . "document_blank.png"; break;
  }
}

function mediatags_mdoctypes($post_item, $size='thumb', $columns)
{
	// info we're going to need
	$parent = get_post( $post_item->post_parent );
  	$authorid = $post_item->post_author;
  	$author = get_the_author_meta('nickname', $authorid);
	$image_src = wp_get_attachment_url($post_item->ID, $size);
	$mimetype = get_post_mime_type( $post_item );
	$filesize = filesize( get_attached_file( $post_item->ID ) );
  	// Yes, you could condense the next few lines but this is to make it easier to follow
  	$filesize_kb = $filesize / 1024;
  	$filesize_kb_rounded = round($filesize_kb);
  	$mt_returned_data = '<tr id="media-tag-item-'.$post_item->ID.'">';
	//let's deal with the columns again
	$mdoc_array = mediatags_cleanColumns($columns);
  	foreach ($mdoc_array as $key => $value) {
	  switch ($value) {
		case "icon": $mt_returned_data .= '<td><img class="filetype-icon" src="'.mediatags_get_icon_for_attachment($post_item).'" alt="'.$post_item->post_title.'" /></td>';
			break;
		case "author": $mt_returned_data .= '<td>'.$author.'</td>';
			break;
		case "filename": $mt_returned_data .= '<td><a href="'.$image_src.'">'.$post_item->post_title.'</a></td>';
			break;
		case "filesize": $mt_returned_data .= '<td>'.$filesize_kb_rounded.' KB</td>';
			break;
		case "thumb": $mt_returned_data .= '<td><img src="'.$image_src.'" title="'.$post_item->post_title.'" /></td>';
			break;
		case "meta": $mt_returned_data .= '<td>'.mediatag_item_callback_show_meta($post_item, $size='thumb').'</td>';
			break;
	  }
	}
	$mt_returned_data .= '</tr>';
	return $mt_returned_data;
}
function mediatags_validElement($element) {
  // selected whitelist of columns
    return $element == "icon" || $element == "filename" || $element == "author" || $element == "filesize" || $element == "meta" || $element == "thumb";
}
function mediatags_cleanColumns($incoming) {
	// if not set or empty, set default values
	$mdoc_default = "icon,filename,author,filesize"; 
	if (!isset($incoming)){
		//let's set some defaults here, being lazy with array creation
		$mdoc_array = explode(",",$mdoc_default);
	}
	  else {
		$mdoc_array = explode(",",$incoming);
		//remove any values we don't know with our whitelisting function
		$mdoc_array_sanitised = array_values(array_filter($mdoc_array, "mediatags_validElement"));
	  }
	//wait a minute, what if no valid columns were set?
	  if (empty($mdoc_array_sanitised)) {
		$mdoc_array_sanitised = explode(",",$mdoc_default);
		}
 return $mdoc_array_sanitised;
}

function mediatags_column_header($atts) {
  $incoming = $atts['columns'];
  $output = '<table class="display" id="mt_mdoctypes"><thead>
			<tr>';
  $mdoc_array_sanitised = mediatags_cleanColumns($incoming);
  // start for each loop
  foreach ($mdoc_array_sanitised as $key => $value) {
	// output the corresponding row
	$output .="<th>".$value."</th>";
  }
  // add the closing table header tags
  $output .= "</tr>
		</thead>
		<tbody>";
  return $output;
}
?>
