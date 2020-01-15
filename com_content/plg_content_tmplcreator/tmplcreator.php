<?php

/**

 * @copyright	Copyright (c) 2018 Crock. All rights reserved.

 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

 */



// no direct access

defined('_JEXEC') or die;



jimport('joomla.plugin.plugin');



/**

 * content - tmplcreator Plugin

 *

 * @package		Joomla.Plugin

 * @subpakage	Crock.tmplcreator

 */

class plgcontenttmplcreator extends JPlugin {



	/**

	 * Constructor.

	 *

	 * @param 	$subject

	 * @param	array $config

	 */

	function __construct(&$subject, $config = array()) {

		// call parent constructor

		parent::__construct($subject, $config);

	}



	public function onAjaxTmplcreator(){ 
	// plugin params
		$params = $this->params;
		
			require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'query.php');
		
			$jinput = JFactory::getApplication();
		
			$category = JCategories::getInstance('Content');

	// get event dispatcher and load content plugins

			$dispatcher = JEventDispatcher::getInstance();

			JPluginHelper::importPlugin('content');

	// order *****

			$orderby   = ' ';

			$articleOrderby   = $params->get('orderby_sec', 'rdate');

			$articleOrderDate = $params->get('order_date');

			$categoryOrderby  = $params->def('orderby_pri', '');

			$secondary        = ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';

			//$primary          = ContentHelperQuery::orderbyPrimary($categoryOrderby);

			$orderby .= $primary . ' ' . $secondary . ' a.created ';

// order end *****

			JLoader::import('joomla.application.component.model');

			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');

	//POST *****

		$pagin_psrv = $jinput->input->get('pagin_psrv');

		$add_page_psrv = $jinput->input->get('add_page_psrv');

		$start_psrv = $jinput->input->get('start_psrv');

		$catId = $jinput->input->get('catid');

	//**********

	

	$cat_deep = count($category->get($catId)->getChildren(true));

			$model = JModelLegacy::getInstance('Articles', 'ContentModel');

			$model->getState();

			$model->setState('list.limit', $add_page_psrv); //set Limit on page

			$model->setState('params', JFactory::getApplication()->getParams());

			$model->setState('filter.category_id', $catId);

	 $model->setState('filter.subcategories', true);

	$model->setState('filter.max_category_levels', $cat_deep);

			$model->setState('list.ordering', $orderby); 

			$model->setState('list.start', $start_psrv); // set item start on page

			$model->setState('filter.published', 1);

			$articles = $model->getItems();

	

		$site_name = JURI::root();

		$site_name = rtrim($site_name,"/");

	

$text = '';	

		foreach ($articles as $item){

		//trigger event for plugin Tags Order	

		$dispatcher->trigger('onContentPrepare', array ('com_content.tagsordering', &$item, &$item->params, $offset=null));
		$dispatcher->trigger('onContentPrepare', array ('com_content.cackle', &$item, &$item->params, $offset=null));

		// get links	

		$link_article = JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid, $item->language));

		$link_category = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid, $item->language));

		

			$url = $site_name.$link_article;

		$pagin_psrv++;

		$img_psrv  = json_decode($item->images)->image_intro;



			

		if ($img_psrv) {

			$img_psrv = $img_psrv;

		}else {		// added by Crock 23-12-17 *** get images from content if image_intro is empty ***

			preg_match_all('/<img .*?(?=src)src=\"([^\"]+)\"/si',$item->introtext,$matches);

			if(!$matches[1][0]) preg_match_all('/<img .*?(?=src)src=\"([^\"]+)\"/si',$item->fulltext,$matches);

			$img_psrv = $matches[1][0];	

		}

			

	// added Crock 10-05-2018 *** get iframe if image doesn't exist (&& line 193)

			

			if (!$matches[1][0])preg_match_all('/<iframe .*?(?=src)src=\"([^\"]+)\"/si',$item->introtext,$matches_iframe);	

			if (!$matches_iframe[1][0]) preg_match_all('/<iframe .*?(?=src)src=\"([^\"]+)\"/si',$item->fulltext,$matches_iframe);

			$iframe_psrv = 	$matches_iframe[1][0];	

			

		// // get image from API google

			preg_match('/.*?list=([^\?\&]+)/',$iframe_psrv,$matches_playlistid); // get playlistId

			if ($matches_playlistid[1]) {

				//for v3 API google

				// request ytb data

				$API_key = 'AIzaSyCxzaPN-N0wnZxgusmfWuv7tQoMiX3A9Do';

				$url = 'https://www.googleapis.com/youtube/v3/playlistItems?playlistId='.$matches_playlistid[1].'&maxResults=1&part=snippet&key='.$API_key;

				$playlistitems = json_decode(file_get_contents($url), true);

				$iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['maxres']['url'];

					if(!$iframe_psrv) $iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['standard']['url'];

						if(!$iframe_psrv) $iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['default']['url'];

			} else {

			if (!$matches[1][0]) preg_match_all('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i',$item->introtext, $matches_iframe);

			if (!$matches_iframe[1][0]) preg_match_all('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i',$item->fulltext,$matches_iframe);

			$iframe_psrv = 'https://img.youtube.com/vi/'.$matches_iframe[6][0].'/0.jpg';

			}

			

			$text.='

			<div class="psrv_item" >

		

			<div class="psrv_item_inner_cont" >

				<div class="psrv_cover" >

					<div style="position: relative;" class="psrv_img">

						<div style="min-height: 250px;" class="psrv_img_item">

							';

			if ($img_psrv ) { $text .='<a href="'.$link_article.'"><img src="/'.$img_psrv.'"></a>';

											 }else {

				//$text .= '<iframe src="'.$iframe_psrv.'" height="250" frameborder="0"></iframe>'; 			//for ytb video

				$text .='<a href="'.$link_article.'"><img src="'.$iframe_psrv.'"></a>';	// for ytb img

			}

				

					$text .=	'</div>

						<div style="position: absolute; top: 210px; right: 50px;"  "class="psrv_soc_icons">

							<span  class="item-social-icons" onClick="soc_icons(this)" >

						<i class="micon">share</i>

						<a  onclick="window.open(\'https://www.facebook.com/sharer/sharer.php?u='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Facebook" class="facebook icon-share-popup"><i class="fa fa-facebook"></i></a>	

						<a  onclick="window.open(\'https://www.twitter.com/intent/tweet?url='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Twitter" class="twitter icon-share-popup"><i class="fa fa-twitter"></i></a>

						<a  onclick="window.open(\'https://plus.google.com/share?url='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Google+" class="google icon-share-popup"><i class="fa fa-google-plus"></i></a>

						<a  onclick="window.open(\'whatsapp://send?text='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Whatsapp" class="whatsapp icon-share-popup"><i class="fa fa-whatsapp"></i></a>

						<a  onclick="window.open(\'https://web.skype.com/share?url='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Skype" class="skype icon-share-popup"><i class="fa fa-skype"></i></a>

						<a  onclick="window.open(\'https://vk.com/share.php?url='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="VK" class="vk icon-share-popup"><i class="fa fa-vk"></i></a>

						<a  onclick="window.open(\'viber://forward?text='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Viber" class="viber icon-share-popup"><i class="fa fa-viber"></i></a>

						<a  onclick="window.open(\'https://t.me/share/url?url='.$url.'\',\'newwindow\', \'width=548, height=325\');" title="Telegram" class="telegram icon-share-popup"><i class="fa fa-telegram"></i></a>

							</span>

						</div>

					</div>

				</div>

				<div class="psrv_details">

					<div class="psrv_details_date">'

						.JHtml::_("date",$item->publish_up,"d.m.Y").'

					</div>

					<div class="psrv_details_info">

						<a href="'.$link_category.'">

						'.$category->get($item->catid)->title.'</a><span class="info_psrv"><i class="fa fa-eye">'.$item->hits.'</i><i><img src="/images/site/icon/comments.png" width="15">'.$item->count.'</i></span>

					</div>

					<div class="psrv_details_title">

						<h3>

						<a href="'.$link_article.'">'.$item->title.'</a>

						</h3>

					</div>

					<div class="psrv_details_desc 1">'

						//.preg_replace("/<p><img[^>]+\><\/p>/i", "",$item->introtext).' 

						.'<p>'.JHTML::_('string.truncate',$item->introtext, 200, true,false).'</p>	

							

					</div>

					<div class="psrv_details_tags">';

			if($item->tags->itemTags){

			JLoader::register('TagsHelperRoute', JPATH_BASE .'/components/com_tags/helpers/route.php');

				foreach($item->tags->itemTags as $tag){

					$text .= '<span><a href="'.JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . ':' . $tag->alias)).'">#'.$tag->title.'</a></span>';

				}

			}

					

			$text .='		

					</div>

				</div>

			</div>

		

	</div>';

	

		}

		$return_array = array(

		'pagin'=>$pagin_psrv,

		'text'=>$text,

		'catdeep'=>$cat_deep);

		//echo json_encode($return_array);

		return $return_array;

		

	}

	

	

}