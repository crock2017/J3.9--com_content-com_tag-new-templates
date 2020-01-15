<?php
/**
 * @package     Joomla.Site
 * @ crock@vodafone.de
 * @ August 2017
 * @copyright   Copyright (C) 2017 Crock, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// begin
// load jQuery if need
			//JHtml::_('jquery.framework');
// load awesome font if need
$doc = JFactory::getDocument();
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/font-awesome.css");
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/sharesocicons.css");
$doc->addStyleSheet("components/com_minitekwall/assets/css/assets/fa-viber.css");

			$categ = new ContentModelCategory;
//params

$plugin = JPluginHelper::getPlugin('content', 'tmplcreator');
$params = new JRegistry($plugin->params);

		//	$params    = JComponentHelper::getParams('com_content');
			$total_psrv = $this->pagination->total;
			$start_psrv = $params->get('cat_list_item_start_psrv'); // from what article is began == 0 - is first
			$page_psrv = $params->get('cat_list_item_page_psrv'); // load in first on page
			$add_page_psrv = $params->get('cat_list_item_add_page_psrv'); // next quantities to load on page
			$back_ground_psrv = $params->get('cat_list_item_background');
			$pagin_psrv=0; // increament - how mutch is loaded on page
			$catId = $categ->getCategory()->id;

//filters
$filters_ids = json_decode(json_encode($this->state), true);
	if (isset($filters_ids['fieldsandfilters.itemsID'])){ // if filter is insirted
$filters_ids_array = $filters_ids['fieldsandfilters.itemsID'];
		//print_r($filters_ids_array);
$url = JRoute::_('index.php?option=com_fieldsandfilters&task=cat_psrv&format=row');
				}else{
$filters_ids_array = 0;
//$url  = JRoute::_('index.php?option=com_content&task=cat_psrv&format=row');
		$url  = JRoute::_('index.php?option=com_ajax&plugin=tmplcreator&group=content&format=json');
}

?>
<div id="container_cat" class="psrv_container_cats">
<?php echo $article->title; ?>

</div>
<div class="more_result_psrv">
	<a id="btn_psrv" >Показать еще<?php// echo ' ( '.$total_psrv.' )'; ?></a>
</div>


<script>
// Global variables
	var back_ground_psrv = '<?php echo $back_ground_psrv; ?>' ;
	var total_psrv = <?php echo $total_psrv; ?>;
	var id 	= <?php echo $catId; ?>; // recent CAT id
	var add_page_psrv = <?php echo $add_page_psrv;?> ; // quantity to add into new page
	var page_psrv = <?php echo $page_psrv;?> ;  // start page of items
	var pagin_psrv = <?php echo $pagin_psrv;?> ; // start increament of pagination
	var start_psrv = <?php echo $start_psrv ?> ; // start item on page
	// filters
	var filters_ids = <?php echo json_encode($filters_ids_array) ?>;
	var url_psrv = '<?php echo $url ?>'; // ajax url
	
jQuery(function(d) {
// first page load	
	d(document).ready(function(){
		var postdata_psrv = {
			add_page_psrv : page_psrv,
			pagin_psrv: pagin_psrv,
			start_psrv: start_psrv,
			catid	:	id,
			filters_ids : filters_ids
		};
	d.ajax({
			type: 		'POST',
			url:		url_psrv,
			data:		postdata_psrv,
			success: 	function(begin){
				//begin = JSON.parse(begin.data[0]); 
				begin = begin.data[0]; 
				d('#container_cat').append(begin.text);
				var n = total_psrv - page_psrv;
				var n_text = 'Показать еще';//"(' +(n)+')';
				if (n > 0 && begin.text !=='') n = n; 
				else {
					n_text ='Показано все';
					d('#btn_psrv').off('click');
				}
				d('#btn_psrv').text(n_text);
				d('.psrv_item_inner_cont').css('background-color', back_ground_psrv);
				
			}
	}); 
	}); 
	 
// add loard more	
d('#btn_psrv').on('click', function(){
	
	var postdata_psrv_second = {
			add_page_psrv : add_page_psrv,
			pagin_psrv: pagin_psrv,
			start_psrv: start_psrv + page_psrv,
			catid	:	id,
			filters_ids : filters_ids
		};
	
		d.ajax({
			type: 		'POST',
			url:		url_psrv,
			data:		postdata_psrv_second,
			beforeSend : function () {
			d('#btn_psrv').text('');
			d('#btn_psrv').append('<span id="load_psrv"><i class="fa fa-cog fa-spin fa-lg "></i></span>');
				},
			success: 	function(data){
				//data = JSON.parse(data);
				data = data.data[0];
				d('#container_cat').append(data.text);
				start_psrv  = data.pagin; // start for recent page
				pagin_psrv = start_psrv; // new pagin icreament
				var n = total_psrv - page_psrv - start_psrv ;
				var n_text = 'Показать еще';// (' +(n)+')';
				if (n > 0 && data.text !== '') n = n; 
				else {
					n_text ='Показано все';
					d('#btn_psrv').off('click');
				}
				d('#btn_psrv').text(n_text);
				d('.psrv_item_inner_cont').css('background-color', back_ground_psrv);
			}
		});
		
});	
})
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
		height: 209px;
		overflow: hidden;
	}
	.psrv_img_item img {
		vertical-align: middle;
		border: 0;
		padding-bottom: 10px;
		width: 100%;
		height: 100%;
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
		line-height: 18px;
		text-transform: none;
		color: #333;
		font-size: 16px;
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
		padding: 5px 15px 0px 15px;
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
		padding: 1px 15px;
		
	}
	
	.psrv_details_desc{
    height: 55px;
    overflow: hidden;
		
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
	@media only screen and (max-width: 660px){
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
/* tags styles */
	.psrv_details_tags span{
    margin-right: 10px;
    display: inline-block;
}
.psrv_details_tags{
    margin: 10px 0px 0px 15px;
}
.psrv_details_tags a{
    color: #666;
    font-size: 11px;
    font-weight: bold;
}
.psrv_details_tags a:hover {
		border-bottom: #666 thin dotted;
	}
	
</style>
