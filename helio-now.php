<?php

/**
Plugin Name: Helio Now
Description: Helio Now is a Widget which displays information from observatories watched the Sun. It displays the latest photo of the sun, made in the observatory, which can be selected in the plugin settings.
Version: 0.1
Author: Limeira Studio
Author URI: http://www.limeirastudio.com/
License: GPL2
Copyright: Limeira Studio
*/

function register_hn_widget()	{
	register_widget('Helio_Now');
}
add_action('widgets_init', 'register_hn_widget');

class Helio_Now extends WP_Widget {
		
	private $defaults;
	
	private $layers = array(
		'AIA 131'			=> '[SDO,AIA,AIA,131,1,100]',
		'AIA 171'			=> '[SDO,AIA,AIA,171,1,100]',
		'AIA 193'			=> '[SDO,AIA,AIA,193,1,100]',
		'AIA 211'			=> '[SDO,AIA,AIA,211,1,100]',
		'AIA 304'			=> '[SDO,AIA,AIA,304,1,100]',
		'AIA 335'			=> '[SDO,AIA,AIA,335,1,100]',
		'EIT 171'			=> '[SOHO,EIT,EIT,171,1,100]',
		'EUVI-B 171'		=> '[STEREO_B,SECCHI,EUVI,171,1,100]',
		'SWAP 174'			=> '[PROBA2,SWAP,SWAP,174,1,100]',
		'SXT AlMgM'			=> '[Yohkoh,SXT,SXT,AlMgMn,1,100]',
		'HMI Continuum'		=> '[SDO,HMI,HMI,continuum,1,100]',
		'HMI Magnetogram'	=> '[SDO,HMI,HMI,magnetogram,1,100]'
	);
	
	private $observatories = array(
		'SOHO'		=> 'Solar and Heliospheric Observatory',
		'SDO'		=> 'Solar Dynamics Observatory',
		'STEREO_A'	=> 'Solar Terrestrial Relations Observatory Ahead',
		'STEREO_B'	=> 'Solar Terrestrial Relations Observatory Behind',
		'PROBA2'	=> 'Project for OnBoard Autonomy 2',
		'Yohkoh'	=> 'Yohkoh (Solar-A)'
	);
	
	function __construct()	{
		$options = array(
            'description'   =>  'Helio Now is a Widget which displays information from observatories watched the Sun.',
            'name'          =>  'Helio Now'
        );
		
		parent::__construct('hv', '', $options);
		
		$this->defaults =  array(
		'title'						=> 'Helio Now',
		'watermark'					=> '',
		'display_data'				=> 'on',
		'display_observatory_title'	=> 'on',
		'bg'						=> '1'
		);
	}
	
	public function form($instance)	{

		$instance = wp_parse_args((array)$instance, $this->defaults);
		$title = ! empty($instance['title']) ? $instance['title'] : '';
		$layers = ! empty($instance['layers']) ? $instance['layers'] : '';
		$watermark = ! empty($instance['watermark']) ? $instance['watermark'] : '';
		$display_data = ! empty($instance['display_data']) ? $instance['display_data'] : '';
		$bg_color = ! empty($instance['bg_color']) ? $instance['bg_color'] : '';
		$display_observatory_title = ! empty($instance['display_observatory_title']) ? $instance['display_observatory_title'] : '';
		?>
		<p>
			<label for="<?=$this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?=$this->get_field_id('title'); ?>" name="<?=$this->get_field_name('title'); ?>" type="text" value="<?=esc_attr($title); ?>">
		</p>
		Image datasource layer(s):
		<p>
		<select name="<?=$this->get_field_name('layers'); ?>" id="<?=$this->get_field_id('layers'); ?>">
			<?php foreach($this->layers as $k=>$v) : ?>
				<option <?php selected($v, $layers); ?> value="<?=$v;?>"><?=$k;?></option>
			<?php endforeach; ?>
		</select>
		</p>
		<p>
		<p>
		<input class="checkbox" type="checkbox" <?php checked($display_observatory_title, 'on'); ?> id="<?=$this->get_field_id('display_observatory_title'); ?>" name="<?=$this->get_field_name('display_observatory_title'); ?>" /> 
		<label for="<?=$this->get_field_id('display_observatory_title'); ?>"> Display Observatory Title</label>
		</p>
		<input class="checkbox" type="checkbox" <?php checked($watermark, 'on'); ?> id="<?=$this->get_field_id('watermark'); ?>" name="<?=$this->get_field_name('watermark'); ?>" /> 
		<label for="<?=$this->get_field_id('watermark'); ?>"> Watermark</label>
		</p>
		<p>
		<input class="checkbox" type="checkbox" <?php checked($display_data, 'on'); ?> id="<?=$this->get_field_id('display_data'); ?>" name="<?=$this->get_field_name('display_data'); ?>" /> 
		<label for="<?=$this->get_field_id('display_data'); ?>"> Display Data</label>
		</p>
		<p>
		<?php
		$bg = (isset($instance['bg']) && is_numeric($instance['bg'])) ? (int) $instance['bg'] : 0;
	    for($n = 1; $n < 3; $n++)	{
	    	echo ($n == 1) ? 'Default ' : 'Custom';
	    	echo '<input type="radio" id="'.$this->get_field_id('bg').'-'.$n.'" name="'.$this->get_field_name('bg').'" value="'.$n.'" '. checked($bg == $n, true, false) .'>';
	    }
		echo '<input type="color" id="'.$this->get_field_id('bg_color').'" name="'.$this->get_field_name('bg_color').'" value="'.$bg_color.'"'.'>';
		?>
		</p>
		<?php
		
	}
	
	public function widget($args, $instance)	{

		$title = $instance['title'];
		$layers = $instance['layers'];
		$watermark = ($instance['watermark'] == 'on') ? 'true' : 'false';
		$display_data = $instance['display_data'];
		$display_observatory_title = $instance['display_observatory_title'];
		$bg = ($instance['bg'] == 1) ? 'default' : 'custom';
		
		$data = $this->get_data($layers);
		
		echo $args['before_widget'];?>
		<style>
			.hn-widget h4	{
				color:#777;
				padding: 8px;
			}
			
			.hn-widget	{
				<?php if($bg == 'default'): ?>
				background: url(<?=plugin_dir_url( __FILE__ ) . 'bg.png';?>) repeat scroll 0 0 padding-box #222222;
				<?php else: ?>
				background-color:<?=$instance['bg_color'];?>;
				<?php endif; ?>
				padding: 5px;
				color:#777;
				border-radius:8px;
			}
			.hn-widget-image	{
				width:95%;
				padding: 5px;
			}
			.hn-widget-image:hover	{
				filter: alpha(Opacity=80);
				opacity: 0.8;
			}
			.hn-widget-data	{
				font-size:11px;
				padding: 8px;
				line-height: 12px;
			}
		</style>
		<div class="hn-widget">
		<?php
		if($title)	{
			echo '<h3 class="hn-widget-title">'.$title.'</h3>';
		}
		if($display_observatory_title)	{
			echo '<h4>'.$this->observatories[$data['Observatory']].'</h4>';
		}
		$url = 'http://helioviewer.org/api/v1/takeScreenshot/?date='.$this->get_now().'&imageScale=2.4204409&layers='.$layers.'&eventsLabels=true&x0=0&y0=0&width=1120&height=980&display=true&watermark='.$watermark;
		echo '<a title="'.$this->get_alt($data).'" href="'.$url.'"><img class="hn-widget-image" src="'.$url.'" alt="'.$this->get_alt($data).'" /></a>';		
		if($display_data)	{
			echo '<div class="hn-widget-data">';
			echo 'Observatory: '.$data['Observatory'].'<br/>';
			echo 'Instrument: '.$data['Instrument'].'<br/>';
			echo 'Detector: '.$data['Detector'].'<br/>';
			echo 'Measurement: '.$data['Measurement'].'<br/>';
			echo '</div>';
		}?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function update($new_instance, $old_instance)	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['layers'] = (isset($new_instance['layers'])) ? strip_tags($new_instance['layers']) : '';
		$instance['watermark'] = (isset($new_instance['watermark'])) ? strip_tags($new_instance['watermark']) : '';
		$instance['display_data'] = (isset($new_instance['display_data'])) ? strip_tags($new_instance['display_data']) : '';
		$instance['display_observatory_title'] = (isset($new_instance['display_observatory_title'])) ? strip_tags($new_instance['display_observatory_title']) : '';
		$instance['bg'] = (isset($new_instance['bg'])) ? strip_tags($new_instance['bg']) : '';
		$instance['bg_color'] = (isset($new_instance['bg_color'])) ? strip_tags($new_instance['bg_color']) : '';
		
		return $instance;
	}

	private function get_now()	{
		return date("Y-m-d").'T'.date("H:i:s").'Z';
	}
	
	private function get_data($layers)	{
		list($observatory, $instrument, $detector, $measurement) = explode(',', $layers);
		$observatory = str_replace('[', '', $observatory);
		return array('Observatory'=>$observatory, 'Instrument'=>$instrument, 'Detector'=>$detector, 'Measurement'=>$measurement);
	}

	private function get_alt($data)	{
		return $this->observatories[$data['Observatory']].' '.$this->get_now();
	}

}

?>
