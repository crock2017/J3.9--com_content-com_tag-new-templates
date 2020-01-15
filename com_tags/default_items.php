<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags template of crock@vodafone.de
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('jquery.framework', false); // load jquery
// load awesome font and social icons styles if need
$doc = JFactory::getDocument();
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/font-awesome.css");
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/sharesocicons.css");
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/fa-viber.css");
// end of  font load


$items = $this->items;
$n = count($this->items);
//jimport('joomla.application.component.helper');
//$app = JFactory::getApplication();
//$content_params  = $app->getParams('com_tags');

$plugin = JPluginHelper::getPlugin('tags', 'tagtmplcreator');
$content_params = new JRegistry($plugin->params);

// **** params of pagination **** look global configuration for Tags
//$tag_list_oderby = $content_params->get('tag_list_orderby');
//$tag_list_oderby_direction = $content_params->get('tag_list_orderby_direction');

$total_psrv = $this->pagination->total;
$start_psrv = $content_params->get('tag_list_item_start_psrv'); // from what article is began == 0 - is first
$page_psrv = $content_params->get('tag_list_item_page_psrv'); // load in first on page
$add_page_psrv = $content_params->get('tag_list_item_add_page_psrv'); // next quantities to load on page
$back_ground_psrv = $content_params->get('tag_list_item_background');
$pagin_psrv=0; // increament - how mutch is loaded on page
$tagId  = $this->item[0]->id; //single Tag

//$url_controller = JRoute::_('index.php?option=com_tags&task=psrv&format=row');
$url_controller = JRoute::_('index.php?option=com_ajax&plugin=tagtmplcreator&group=tags&format=json');

?>
<!-- begin template -->

<div id="container_tag" class="psrv_container_tags">
</div>
<div class="more_result_psrv">
	<a id="btn_psrv" >Показать еще<?php echo ' ( '.$this->remain.' )'; ?></a>
</div>
<!-- end template -->

<script type="text/javascript">
// Global variables
	var back_ground_psrv = '<?php echo $back_ground_psrv; ?>' ;
	var total_psrv = <?php echo $total_psrv; ?>;
	var id 	= <?php echo $tagId; ?>; // recent TAG id
	var add_page_psrv = <?php echo $add_page_psrv;?> ; // quantity to add into new page
	var page_psrv = <?php echo $page_psrv;?> ;  // start page of items
	var pagin_psrv = <?php echo $pagin_psrv;?> ; // start increament of pagination
	var start_psrv = <?php echo $start_psrv ?> ; // start item on page
	var url = '<?php echo $url_controller ?>';

	
jQuery(function(p) {

// first page load	
	p(document).ready(function(){
		var postdata_psrv = {
			add_page_psrv : page_psrv,
			pagin_psrv: pagin_psrv,
			start_psrv: start_psrv,
			tagid	:	id
		};
	p.ajax({
			type: 		'POST',
			url:		url,
			data:		postdata_psrv,
		
			success: 	function(begin){
				//begin = JSON.parse(begin);
				begin = begin.data[0];
				p('#container_tag').append(begin.text);
				var n = total_psrv - page_psrv;
				var n_text = 'Показать еще (' +(n)+')';
				if (n > 0) n = n; 
				else {
					n_text ='Показано все';
					p('#btn_psrv').off('click');
				}
				p('#btn_psrv').text(n_text);
				p('.psrv_item_inner_cont').css('background-color', back_ground_psrv);
				
			}
	}); 
	}); 
	 
// add loard more	
p('#btn_psrv').on('click', function(){
	
	var postdata_psrv_second = {
			add_page_psrv : add_page_psrv,
			pagin_psrv: pagin_psrv,
			start_psrv: start_psrv + page_psrv,
			tagid	:	id
		};
	
		p.ajax({
			type: 		'POST',
			url:		url,
			data:		postdata_psrv_second,
			beforeSend : function () {
			p('#btn_psrv').text('');
			p('#btn_psrv').append('<span id="load_psrv"><i class="fa fa-cog fa-spin fa-lg "></i></span>');
				},
			success: 	function(data){
				//data = JSON.parse(data);
				data = data.data[0];
				p('#container_tag').append(data.text);
				start_psrv  = data.pagin; // start for recent page
				pagin_psrv = start_psrv; // new pagin icreament
				var n = total_psrv - page_psrv - start_psrv ;
				var n_text = 'Показать еще (' +(n)+')';
				if (n > 0) n = n; 
				else {
					n_text ='Показано все';
					p('#btn_psrv').off('click');
				}
				p('#btn_psrv').text(n_text);
				p('.psrv_item_inner_cont').css('background-color', back_ground_psrv);
			}
		})
		
});	
}); // method of tougle class for soc. icons
	function soc_icons(ev){
		ev.classList.toggle('open');
			};
</script>
<style>
	#btn_psrv {
		background-color: #e96d51;
		border: #e96d51 thin solid;
		border-radius: 3px;
		color: #ffffff;
		
	}
	.more_result_psrv a {
		box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.08) inset;
		display: inline-block;
		font-size: 14px;
		line-height: 26px;
		min-width: 100px;
		padding: 8px 14px;
		text-decoration: none;
		vertical-align: top;
	
		
	}
	.more_result_psrv {
		font-weight: 400;
		line-height: 30px;
		margin: 25px 0 30px;
		padding: 0;
		text-align: center;
		vertical-align: baseline;
		
		
	}
	#btn_psrv:hover {
		background-color: #F7F7F7;
		color:#666;
		border-color: #BBBBBB;
		cursor: pointer;
	}
	
	.psrv_item {
		padding: 5px;
	}
	
	.psrv_item_inner_cont {
		height: 100%;
		display: inline-flex;
		width: 100%; /* 19-01-18 */
	}
	.psrv_cover {
		width: 50%;
	}
	.psrv_img_item img {
		vertical-align: middle;
		border: 0;
		padding-bottom: 10px;
		width: 100%;
	}
	.psrv_details {
		width: 50%;
	}
	.psrv_details_date {
		color: #666;
		font-size: 12px;
		padding: 10px 15px 0px 15px;
		text-transform: uppercase;
	}
	.psrv_details_title a {
		line-height: 24px;
		text-transform: uppercase;
		color: #333;
		font-size: 18px;
		text-decoration: none;
	}
	
	.psrv_details_title{
		padding: 10px 15px 0px 15px;
	}
	.psrv_details_info a {
		color: #555;
		text-decoration: none;
		
	}
	.psrv_details_info {
		padding: 10px 15px 0px 15px;
	}
	.psrv_details_info a:hover {
		border-bottom: #666 thin dotted;
		background: none !important;
	}
	.psrv_details_desc p{
		color: #555;
		font-size: 13px;
		font-weight: 400;
		line-height: 18px;
		padding: 10px 15px;
		
	}
	
	}
	.psrv_details_title a:hover {
		background: none !important;
	}
	.info_psrv {
		float: right;
	}
	.info_psrv i {
		margin-right: 15px;
		
	}
	.info_psrv i::before,.info_psrv img {
		padding-right: 3px;
	}
	@media only screen and (max-width: 960px){
		.psrv_cover {
			width: 100% !important;
			max-width: none;
		}
		.psrv_details {
			width:100% !important;
		}
		.psrv_item_inner_cont{
			float: none !important;
			display: block !important; 
		}
	}
	

	
</style>

