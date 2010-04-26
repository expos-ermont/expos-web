<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

# Localized string we find in template
__("This tag's comments Atom feed");
__("This tag's entries Atom feed");


require dirname(__FILE__).'/_widgets.php';

$core->tpl->addBlock('MetaData',array('tplMetadata','MetaData'));
$core->tpl->addBlock('MetaDataHeader',array('tplMetadata','MetaDataHeader'));
$core->tpl->addBlock('MetaDataFooter',array('tplMetadata','MetaDataFooter'));
$core->tpl->addValue('MetaID',array('tplMetadata','MetaID'));
$core->tpl->addValue('MetaPercent',array('tplMetadata','MetaPercent'));
$core->tpl->addValue('MetaRoundPercent',array('tplMetadata','MetaRoundPercent'));
$core->tpl->addValue('MetaURL',array('tplMetadata','MetaURL'));
$core->tpl->addValue('MetaAllURL',array('tplMetadata','MetaAllURL'));
$core->tpl->addValue('TagFeedURL',array('tplMetadata','TagFeedURL'));
$core->tpl->addBlock('EntryMetaData',array('tplMetadata','EntryMetaData'));

$core->addBehavior('templateBeforeBlock',array('behaviorsMetadata','templateBeforeBlock'));

class behaviorsMetadata
{
	public static function templateBeforeBlock(&$core,$b,$attr)
	{
		if (($b == 'Entries' || $b == 'Comments') && isset($attr['tag']))
		{
			return
			"<?php\n".
			"@\$params['from'] .= ', '.\$core->prefix.'meta META ';\n".
			"@\$params['sql'] .= 'AND META.post_id = P.post_id ';\n".
			"\$params['sql'] .= \"AND META.meta_type = 'tag' \";\n".
			"\$params['sql'] .= \"AND META.meta_id = '".$core->con->escape($attr['tag'])."' \";\n".
			"?>\n";
		}
		elseif (empty($attr['no_context']) && ($b == 'Entries' || $b == 'Comments'))
		{
			return
			'<?php if ($_ctx->exists("meta")) { '.
				"@\$params['from'] .= ', '.\$core->prefix.'meta META ';\n".
				"@\$params['sql'] .= 'AND META.post_id = P.post_id ';\n".
				"\$params['sql'] .= \"AND META.meta_type = 'tag' \";\n".
				"\$params['sql'] .= \"AND META.meta_id = '\".\$core->con->escape(\$_ctx->meta->meta_id).\"' \";\n".
			"} ?>\n";
		}
	}
}

class tplMetadata
{
	public static function MetaData($attr,$content)
	{
		$type = isset($attr['type']) ? addslashes($attr['type']) : 'tag';
		
		$limit = isset($attr['limit']) ? (integer) $attr['limit'] : 'null';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		"\$_ctx->meta = \$objMeta->getMeta('".$type."',".$limit."); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .=
		'<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->meta = null; unset($objMeta); ?>';
		
		return $res;
	}
	
	public static function MetaDataHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->meta->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function MetaDataFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->meta->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function EntryMetaData($attr,$content)
	{
		$type = isset($attr['type']) ? addslashes($attr['type']) : 'tag';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		"\$_ctx->meta = \$objMeta->getMetaRecordset(\$_ctx->posts->post_meta,'".$type."'); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .=
		'<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->meta = null; unset($objMeta); ?>';
		
		return $res;
	}
	
	public static function MetaID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->meta->meta_id').'; ?>';
	}
	
	public static function MetaPercent($attr)
	{
		return '<?php echo $_ctx->meta->percent; ?>';
	}
	
	public static function MetaRoundPercent($attr)
	{
		return '<?php echo $_ctx->meta->roundpercent; ?>';
	}
	
	public static function MetaURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("tag").'.
		'"/".rawurlencode($_ctx->meta->meta_id)').'; ?>';
	}
	
	public static function MetaAllURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("tags")').'; ?>';
	}
	
	public static function TagFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'rss2';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'rss2';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("tag_feed")."/".'.
		'rawurlencode($_ctx->meta->meta_id)."/'.$type.'"').'; ?>';
	}
	
	# Widget function
	public static function tagsWidget(&$w)
	{
		global $core;
		
		$limit = abs((integer) $w->limit);
		
		$objMeta = new dcMeta($core);
		$rs = $objMeta->getMeta('tag',$limit);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$sort = $w->sortby;
		if (!in_array($sort,array('meta_id_lower','count'))) {
			$sort = 'meta_id_lower';
		}
		
		$order = $w->orderby;
		if ($order != 'asc') {
			$order = 'desc';
		}
		
		$rs->sort($sort,$order);
		
		$res =
		'<div class="tags">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch())
		{
			$res .=
			'<li><a href="'.$core->blog->url.$core->url->getBase('tag').'/'.rawurlencode($rs->meta_id).'" '.
			'class="tag'.$rs->roundpercent.'">'.
			$rs->meta_id.'</a> </li>';
		}
		
		$res .= '</ul>';
		
		if ($core->url->getBase('tags'))
		{
			$res .=
			'<p><strong><a href="'.$core->blog->url.$core->url->getBase("tags").'">'.
			__('All tags').'</a></strong></p>';
		}
		
		$res .= '</div>';
		
		return $res;
	}
}

class urlMetadata extends dcUrlHandlers
{
	public static function tag($args)
	{
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n)
		{
			self::p404();
		}
		elseif (preg_match('%(.*?)/feed/(rss2|atom)?$%u',$args,$m))
		{
			$type = $m[2] == 'atom' ? 'atom' : 'rss2';
			$mime = 'application/xml';
			$comments = !empty($m[3]);
			
			$objMeta = new dcMeta($GLOBALS['core']);
			$GLOBALS['_ctx']->meta = $objMeta->getMeta('tag',null,$m[1]);
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				self::p404();
			}
			
			$tpl = $type;
			
			if ($type == 'atom') {
				$mime = 'application/atom+xml';
			}
			
			self::serveDocument($tpl.'.xml',$mime);
		}
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			
			$objMeta = new dcMeta($GLOBALS['core']);
			$GLOBALS['_ctx']->meta = $objMeta->getMeta('tag',null,$args);
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				self::p404();
			} else {
				self::serveDocument('tag.html');
			}
		}
		exit;
	}
	
	public static function tags($args)
	{
		self::serveDocument('tags.html');
		exit;
	}
	
	public static function tagFeed($args)
	{
		$mime = 'application/xml';
		
		if (preg_match('#^(.+)/(atom|rss2)(/comments)?$#',$args,$m))
		{
			$tag = $m[1];
			$type = $m[2];
			$comments = !empty($m[3]);
		}
		else
		{
			self::p404();
		}
		
		$objMeta = new dcMeta($GLOBALS['core']);
		$GLOBALS['_ctx']->meta = $objMeta->getMeta('tag',null,$tag);
		$GLOBALS['_ctx']->feed_subtitle = ' - '.__('Tag').' - '.$GLOBALS['_ctx']->meta->meta_id;
		
		if ($GLOBALS['_ctx']->meta->isEmpty()) {
			self::p404();
		}
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$tpl = $type;
		if ($comments) {
			$tpl .= '-comments';
		}
		$tpl .= '.xml';
		
		self::serveDocument($tpl,$mime);
		exit;
	}
}
?>