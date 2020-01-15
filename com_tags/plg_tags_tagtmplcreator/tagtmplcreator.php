<?php

/**

 * @copyright	Copyright (c) 2018 Crock. All rights reserved.

 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

 */



// no direct access

defined('_JEXEC') or die;



jimport('joomla.plugin.plugin');



/**

 * tags - tagtmplcreator Plugin

 *

 * @package		Joomla.Plugin

 * @subpakage	Crock.tagtmplcreator

 */

class plgtagstagtmplcreator extends JPlugin {



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
	
	public function onAjaxTagtmplcreator(){
		require_once (JPATH_SITE.DS.'components'.DS.'com_tags'.DS.'helpers'.DS.'route.php');
		// plugin params
		$params = $this->params;
		$jinput = JFactory::getApplication();
		
		// *** get Params
		//$content_params  = JComponentHelper::getParams('com_tags');
		$tag_list_oderby = $params->get('tag_list_orderby');
		$tag_list_oderby_direction = $params->get('tag_list_orderby_direction');
		//$tag_list_item_img = $content_params->get('tag_list_item_img');
// for object items look /libraries/src/Helper/TagsHelper.php method: getTagItemsQuery()		
		//***
		$text = '';
		$db = JFactory::getDbo();
		$category = JCategories::getInstance('Content');
		$pagin_psrv = $jinput->input->get('pagin_psrv');
		$add_page_psrv = $jinput->input->get('add_page_psrv');
		$start_psrv = $jinput->input->get('start_psrv');
		$tag_id = $jinput->input->get('tagid');
		
		//$tagsHelper = new JHelperTags;
		
		//$query = $tagsHelper->getTagItemsQuery($tag_id, [1], 0, $tag_list_oderby , $tag_list_oderby_direction, 'all','1'  ); 
		$query = self::Query($tag_id, [1], 0, $tag_list_oderby , $tag_list_oderby_direction, 'all','1'  );
		
		$db->setQuery($query, $start_psrv,$add_page_psrv);
		$items = $db->loadObjectList(); 
		// site link for soc.icons
		$site_name = JURI::root();
		$site_name = rtrim($site_name,"/");
		
	
		
		foreach ($items as $i=>$item){
		
		$link_cat = JRoute::_('index.php?option=com_content&view=category&layout=blog&id='.$item->core_catid);	
		$link_item = JRoute::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router));
			$url = $site_name.$link_item;
	// added by Crock 23-12-17, if image_intro is absended
		$image_psrv = json_decode($item->core_images)->image_intro;
		if (!$image_psrv){	// get image from full text
			preg_match_all('/<img .*?(?=src)src=\"([^\"]+)\"/si',$item->ft,$matches);
			$image_psrv = $matches[1][0];	
			if(!$image_psrv) { preg_match_all('/<img .*?(?=src)src=\"([^\"]+)\"/si',$item->it,$matches);
			$image_psrv = $matches[1][0];				 
							 }
			
	// added Crock 10-05-2018 *** get iframe if image doesn't exist (&& line 109 )
			if (!$matches[1][0])preg_match_all('/<iframe .*?(?=src)src=\"([^\"]+)\"/si',$item->it,$matches_iframe);	
			if (!$matches_iframe[1][0]) preg_match_all('/<iframe .*?(?=src)src=\"([^\"]+)\"/si',$item->ft,$matches_iframe);
			$iframe_psrv = 	$matches_iframe[1][0];	
			
			// get image from API google
			preg_match('/.*?list=([^\?\&]+)/',$iframe_psrv,$matches_playlistid); // get playlistId
			if ($matches_playlistid[1]){ 
		//for v3 API google
				// request ytb data
				$API_key = 'AIzaSyCxzaPN-N0wnZxgusmfWuv7tQoMiX3A9Do';
				$url = 'https://www.googleapis.com/youtube/v3/playlistItems?playlistId='.$matches_playlistid[1].'&maxResults=1&part=snippet&key='.$API_key;
				
				$playlistitems = json_decode(file_get_contents($url), true);
				//print_r($playlistitems);
				$iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['maxres']['url'];
					if(!$iframe_psrv) $iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['standard']['url'];
						if(!$iframe_psrv) $iframe_psrv = $playlistitems['items'][0]['snippet']['thumbnails']['default']['url'];
			} else {
			
		// for old API google
			if (!$matches[1][0]) preg_match_all('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i',$item->it, $matches_iframe);
			if (!$matches_iframe[1][0]) preg_match_all('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i',$item->ft,$matches_iframe);
			$iframe_psrv = 'https://img.youtube.com/vi/'.$matches_iframe[6][0].'/0.jpg';
			
			}
		}
	// end 23-12-17		
		$pagin_psrv++;
			$text.='
			<div class="psrv_item" >
		
			<div class="psrv_item_inner_cont" >
				<div class="psrv_cover" >
					<div style="position: relative;" class="psrv_img">
						<div style="min-height: 250px;" class="psrv_img_item">';
			
		if ($image_psrv ) { $text .='<a href="'.$link_item.'"><img src="/'.$image_psrv.'" width="" height="250"></a>';
											 }else {
			//	$text .= '<iframe src="'.$iframe_psrv.'" height="250" frameborder="0"></iframe>';				// for ytb video
				$text .='<a href="'.$link_item.'"><img src="'.$iframe_psrv.'" width="" height="250"></a>';	// for ytb img
			}
		// remove short code for ytb plugin play lists 	
	$item->core_body = preg_replace('/{ytb-menu-pl}/', '', $item->core_body);						
				$text .= '</div>
						<div style="position: absolute; bottom: 20px; right: 60px;" class="psrv_soc_icons">
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
						.JHtml::_("date",$item->core_created_time,"M, d, Y").'
					</div>
					<div class="psrv_details_info">
						<a href="'.$link_cat.'">
						'.$category->get($item->core_catid)->title.'</a><span class="info_psrv"><i class="fa fa-eye">'.$item->hits.'</i><i><img src="/images/site/icon/comments.png" width="15">'.$item->count.'</i></span>
					</div>
					<div class="psrv_details_title">
						<h3>
						<a href="'.$link_item.'">'.$item->core_title.'</a>
						</h3>
					</div>
					<div class="psrv_details_desc">'
						//.preg_replace("/<p><img[^>]+\><\/p>/i", "",$item->core_body).'
							.'<p>'.JHTML::_('string.truncate',$item->core_body, 200, true,false).'</p>
							
					</div>
				</div>
			</div>
		
	</div>';
	
		}
		$return_array = array(
		'pagin'=>$pagin_psrv,
		'text'=>$text);
		//echo json_encode($return_array);
		return $return_array;
		
		
	}

	public function Query($tagId, $typesr = null, $includeChildren = false, $orderByOption = 'c.core_title', $orderDir = 'ASC',
		$anyOrAll = true, $languageFilter = 'all', $stateFilter = '0,1')
	{
		$tagsHelper = new JHelperTags;
		
		// Create a new query object.
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = \JFactory::getUser();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate = $db->quote(\JFactory::getDate()->toSql());

		// Force ids to array and sanitize
		$tagIds = (array) $tagId;
		$tagIds = implode(',', $tagIds);
		$tagIds = explode(',', $tagIds);
		//$tagIds = ArrayHelper::toInteger($tagIds);

		$ntagsr = count($tagIds);

		// If we want to include children we have to adjust the list of tags.
		// We do not search child tags when the match all option is selected.
		if ($includeChildren)
		{
			$tagTreeArray = array();

			foreach ($tagIds as $tag)
			{
				$tagsHelper->getTagTreeArray($tag, $tagTreeArray);
			}

			$tagIds = array_unique(array_merge($tagIds, $tagTreeArray));
		}

		// Sanitize filter states
		$stateFilters = explode(',', $stateFilter);
		//$stateFilters = ArrayHelper::toInteger($stateFilters);

		// M is the mapping table. C is the core_content table. Ct is the content_types table.
		$query
			->select(
				'm.type_alias'
				. ', ' . 'm.content_item_id'
				. ', ' . 'm.core_content_id'
				. ', ' . 'count(m.tag_id) AS match_count'
				. ', ' . 'MAX(m.tag_date) as tag_date'
				. ', ' . 'MAX(c.core_title) AS core_title'
				. ', ' . 'MAX(c.core_params) AS core_params'
			)
			->select('MAX(c.core_alias) AS core_alias, MAX(c.core_body) AS core_body, MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access, MAX(cont.hits) AS hits, MAX(cont.fulltext) AS ft, MAX(cont.introtext) AS it, COUNT(cacl.message) AS count') // added "MAX(cont.hits) AS hits" && "MAX(cont.fulltext) AS ft" && MAX(cont.introtext) AS it && "COUNT(cacl.message) AS count"plus see line 599,600 - for Pisarev G by Crock
			->select(
				'MAX(c.core_metadata) AS core_metadata'
				. ', ' . 'MAX(c.core_created_user_id) AS core_created_user_id'
				. ', ' . 'MAX(c.core_created_by_alias) AS core_created_by_alias'
			)
			->select('MAX(c.core_created_time) as core_created_time, MAX(c.core_images) as core_images')
			->select('CASE WHEN c.core_modified_time = ' . $nullDate . ' THEN c.core_created_time ELSE c.core_modified_time END as core_modified_time')
			->select('MAX(c.core_language) AS core_language, MAX(c.core_catid) AS core_catid')
			->select('MAX(c.core_publish_up) AS core_publish_up, MAX(c.core_publish_down) as core_publish_down')
			->select('MAX(ct.type_title) AS content_type_title, MAX(ct.router) AS router')

			->from('#__contentitem_tag_map AS m')
			->join(
				'INNER',
				'#__ucm_content AS c ON m.type_alias = c.core_type_alias AND m.core_content_id = c.core_content_id AND c.core_state IN ('
					. implode(',', $stateFilters) . ')'
					. (in_array('0', $stateFilters) ? '' : ' AND (c.core_publish_up = ' . $nullDate
					. ' OR c.core_publish_up <= ' . $nowDate . ') '
					. ' AND (c.core_publish_down = ' . $nullDate . ' OR  c.core_publish_down >= ' . $nowDate . ')')
			)
			->join('INNER', '#__content_types AS ct ON ct.type_alias = m.type_alias')

->join('LEFT', '#__content AS cont ON cont.id = c.core_content_item_id') // added for Pisarev G by Crock
->join('LEFT', '#__cackle_comments AS cacl ON cacl.post_id = c.core_content_item_id AND cacl.status = 1') // added for Pisarev G by Crock
			
			// Join over categories for get only tags from published categories
			->join('LEFT', '#__categories AS tc ON tc.id = c.core_catid')

			// Join over the users for the author and email
			->select("CASE WHEN c.core_created_by_alias > ' ' THEN c.core_created_by_alias ELSE ua.name END AS author")
			->select('ua.email AS author_email')

			->join('LEFT', '#__users AS ua ON ua.id = c.core_created_user_id')

			->where('m.tag_id IN (' . implode(',', $tagIds) . ')')
			->where('(c.core_catid = 0 OR tc.published = 1)');


		// Optionally filter on language
		if (empty($language))
		{
			$language = $languageFilter;
		}

		if ($language !== 'all')
		{
			if ($language === 'current_language')
			{
				$language = $tagsHelper->getCurrentLanguage();
			}

			$query->where($db->quoteName('c.core_language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		// Get the type data, limited to types in the request if there are any specified.
		$typesarray = $tagsHelper->getTypes('assocList', $typesr, false);

		$typeAliases = array();

		foreach ($typesarray as $type)
		{
			$typeAliases[] = $db->quote($type['type_alias']);
		}

		$query->where('m.type_alias IN (' . implode(',', $typeAliases) . ')');

		$groups = '0,' . implode(',', array_unique($user->getAuthorisedViewLevels()));
		$query->where('c.core_access IN (' . $groups . ')')
			->group('m.type_alias, m.content_item_id, m.core_content_id, core_modified_time, core_created_time, core_created_by_alias, author, author_email');

		// Use HAVING if matching all tags and we are matching more than one tag.
		if ($ntagsr > 1 && $anyOrAll != 1 && $includeChildren != 1)
		{
			// The number of results should equal the number of tags requested.
			$query->having("COUNT('m.tag_id') = " . (int) $ntagsr);
		}

		// Set up the order by using the option chosen
		if ($orderByOption === 'match_count')
		{
			$orderBy = 'COUNT(m.tag_id)';
		}
		else
		{
			$orderBy = 'MAX(' . $db->quoteName($orderByOption) . ')';
		}

		$query->order($orderBy . ' ' . $orderDir);

		return $query;
	}

	

}