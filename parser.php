<?php

$sites = array();

//$sites[]="https://rosenkeller.org/index.html";
$sites[]="http://www.wagnerverein-jena.de/";

function find_program_page($site){
	$xml = new DOMDocument();
	@$xml->loadHTMLFile($site);
	$links = $xml->getElementsByTagName('a');
	$result='';
	foreach ($links as $link){
		$children=$link->childNodes;
		for ($i=0; $i<$children->length; $i++){
			$child=$children->item($i);
			if ($child instanceof DOMText){
				$text=$child->wholeText;
				if (stripos($text, 'Programm')!==false){
					$result=$link->getAttribute('href');
				}
			}
		}
	}
	if (stripos($result, '://')===false){
		$result=dirname($site).'/'.$result;
	}

	return $result;
}

function find_event_pages($page){
	$xml = new DOMDocument();
	@$xml->loadHTMLFile($page);
	$links = $xml->getElementsByTagName('a');
	$result=array();
	foreach ($links as $link){
		$href=$link->getAttribute('href');
		if (stripos($href,'event')!== false){
			if (stripos($href, '://')===false){
				$href=dirname($page).'/'.$href;
			}
			$result[]=$href;
		}
	}
	return $result;
}

function parse_date($text){
	return $text;
}

function parse_tags($text){
	$dummy=explode(' ', $text);
	$result=array();
	foreach ($dummy as $tag){
		$tag=trim($tag);
		if (strlen($tag)>2){
			$result[]=$tag;
		}
	}
	return $result;
}

function parse_event($page){
	$result=array();
	$links=array();
	$imgs=array();
	
	$xml = new DOMDocument();
	@$xml->loadHTMLFile($page);
	print $page."\n";
	
	
	/** Rosenkeller **/
	$data=$xml->getElementsByTagName('i');
	foreach ($data as $info){
		if ($info->attributes){
			foreach ($info->attributes as $attr){
				if ($attr->name == 'class'){
					if (strpos($attr->value, 'fa-calendar') !== false){
						$result['date']=parse_date($info->nextSibling->wholeText);
						break;
					}
					if (strpos($attr->value, 'fa-building') !==false){
						$result['place']=$info->nextSibling->wholeText;
						break;
					}
					if (strpos($attr->value, 'fa-music') !==false){
						$result['tags']=parse_tags($info->nextSibling->wholeText);
						break;
					}
					if (strpos($attr->value, 'fa-money') !==false){
						break;
					}
					if (strpos($attr->value, 'fa-globe') !==false){
						$link=$info->nextSibling;
						if (!isset($link->tagName)){ // link separated by text: skip to link
							$link=$link->nextSibling;
						}
						if (!isset($link->tagName) || $link->tagName != 'a'){ // still no link found: give up
							break;
						}
						$href=trim($link->getAttribute('href'));
						$tx=trim($link->nodeValue);
						$links[$href]=$tx;
						break;
					}						
				}
				print_r($attr);
				die();				
			}
		}
	}
	$images=$xml->getElementsByTagName('img');
	foreach ($images as $image){
		if ($image->hasAttribute('pagespeed_high_res_src')){
			$src=$image->getAttribute('pagespeed_high_res_src');
			if (stripos($src, '://')===false){
				$src=dirname($page).'/'.$src;
			}
			$imgs[]=$src;
		}
	}
	$lis=$xml->getElementsByTagName('li');
	foreach ($lis as $li){
		foreach ($li->attributes as $attr){
			if ($attr->name == 'class' && strpos($attr->value,'active')!==false){
				$result['title']=trim($li->nodeValue);
				break;				
			}
		}
	}
	/** Rosenkeller **/
	/** Wagner **/
	if (!isset($result['title'])){
		$paragraphs=$xml->getElementsByTagName('p');
		foreach ($paragraphs as $paragraph){
			print $paragraph->getAttribute('class')."\n";
		}
	}
	/** Wagner **/

	if (count($links)>0){
		$result['links']=$links;
	}
	if (count($imgs)>0){
		$result['images']=$imgs;
	}
	
	return $result;
}

function store_event($event){

}

foreach ($sites as $site){
	$program_page=find_program_page($site);
	$event_pages=find_event_pages($program_page);
	$events = array();
	foreach ($event_pages as $event_page){
		$event=parse_event($event_page);
		print_r($event);
		//store_event($event);
	}
}


?>