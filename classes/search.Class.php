<?php
require_once(realpath(dirname(__FILE__) . '/../config.php'));
class Search{
	
	function StopWords(){
	return array(
		'I','a','A','about','About','an','An','are','Are',
		'as','As','at','At','be','Be','by','By','com',
		'for','For','from','From','how','How','in','In','is','Is',
		'it','It','of','Of','on','On','or','Or','that','That',
		'the','The','this','This','to','To','was','Was','what','What',
		'when','When','Who','who','will','Will','with','With','use',
		'Use','build','Build','create','Create');
	}
	
	function Filter($text){
		if(strlen($text) > 5){
			$text = trim(stripslashes(htmlspecialchars($text)));
		}else{
			$text = null;
		}
		return $text;
	}
	
	function FullTitleSentence($text){
		$db = new Connect;
		$posts = $db->prepare("SELECT id FROM posts 
		WHERE title LIKE concat('%', :sentence, '%') ORDER BY id DESC");
		$posts -> execute(array('sentence' => $text));
		if($posts -> rowCount() > 0){
			$i = 0;
			while($post = $posts->fetch(PDO::FETCH_ASSOC)){
				$full_title_sentence[$i]['id'] = $post['id'];
				$i++;
			}
		}else{
			$full_title_sentence = null;
		}
		return $full_title_sentence;
	}

	function BreakupTitleSentence($text){
		$db = new Connect;
		$words = str_word_count($text,1);
		$stopwords = $this->StopWords();
		$words = array_merge(array_diff($words, $stopwords));
		
		if($words){
			$i = 0;
			foreach($words as $word){
				$posts = $db->prepare("SELECT id FROM posts 
				WHERE title LIKE concat('%', :word, '%') ORDER BY id DESC");
				$posts->execute(array('word' => $word));
				while($post = $posts->fetch(PDO::FETCH_ASSOC)){
					$breakup_title[$i]['id'] = $post['id'];
					$i++;
				}
			}
			if(isset($breakup_title)){
				$breakup_titles_sentence = array_unique($breakup_title, SORT_REGULAR);
				$breakup_titles_sentence = array_values($breakup_titles_sentence);
			}else{
				$breakup_titles_sentence = null;
			}
		}else{
			$breakup_titles_sentence = null;
		}
		return $breakup_titles_sentence;
	}
	
	function FullDescriptionSentence($text){
		$db = new Connect;
		$posts = $db->prepare("SELECT id FROM posts 
		WHERE short_description LIKE concat('%', :sentence, '%') ORDER BY id DESC");
		$posts->execute(array('sentence' => $text));
		if($posts->rowCount() > 0){
			$i = 0;
			while($post = $posts->fetch(PDO::FETCH_ASSOC)){
				$sentence[$i]['id'] = $post['id'];
				$i++;
			}
		}else{
			$sentence = null;
		}
		return $sentence;
	}

	function BreakupDescriptionSentence($text){
		$db = new Connect;
		$words = str_word_count($text,1);
		$stopwords = $this->StopWords();
			$words = array_merge(array_diff($words, $stopwords));

		if($words){
			$i = 0;
			foreach($words as $word){
				$posts = $db->prepare("SELECT id FROM posts 
				WHERE short_description LIKE concat('%', :word, '%') ORDER BY id DESC");
				$posts->execute(array('word' => $word));
				while($post = $posts->fetch(PDO::FETCH_ASSOC)){
					$result[$i]['id'] = $post['id'];
					$i++;
				}
			}
			if(isset($result)){
				$final_result = array_unique($result, SORT_REGULAR);
				$final_result = array_values($final_result);
			}else{
				$final_result = null;
			}
		}else{
			$final_result = null;
		}
		return $final_result;
	}
	
	function SearchSpider($text){
		$db = new Connect;
		$text = $this->Filter($text);
		if($text != ''){
			$full_title_sentence = $this->FullTitleSentence($text);
			$breakup_titles_sentence = $this->BreakupTitleSentence($text);
			
			$full_title_sentence = isset($full_title_sentence) ? $full_title_sentence : '';
			$breakup_titles_sentence = isset($breakup_titles_sentence) ? $breakup_titles_sentence : '';
			
			if($full_title_sentence && $breakup_titles_sentence){
				// assign the $breakup_titles_sentence to $full_title_sentence
				$k = count($full_title_sentence);
				for($j=0;$j<count($breakup_titles_sentence);$j++){
					$full_title_sentence[$k] = $breakup_titles_sentence[$j];
					$k++;
				}
				
				// remove double ids from result
				$title_result = array_unique($full_title_sentence, SORT_REGULAR);
				$title_result = array_values($title_result);
			}
			
			if($full_title_sentence && !$breakup_titles_sentence){
				$title_result = $full_title_sentence;
			}
			
			if(!$full_title_sentence && $breakup_titles_sentence){
				$title_result = $breakup_titles_sentence;
			}
			
			$full_descr_sentence = $this->FullDescriptionSentence($text);
			$breakup_descr_sentence = $this->BreakupDescriptionSentence($text);
			
			$full_descr_sentence = isset($full_descr_sentence) ? $full_descr_sentence : '';
			$breakup_descr_sentence = isset($breakup_descr_sentence) ? $breakup_descr_sentence : '';
			
			if($full_descr_sentence && $breakup_descr_sentence){
				// assign the $breakup_descr_sentence to $full_descr_sentence
				$k = count($full_descr_sentence);
				for($j=0;$j<count($breakup_descr_sentence);$j++){
					$full_descr_sentence[$k] = $breakup_descr_sentence[$j];
					$k++;
				}
				
				// remove double ids from result
				$description_result = array_unique($full_descr_sentence, SORT_REGULAR);
				$description_result = array_values($description_result);
			}
			
			if($full_descr_sentence && !$breakup_descr_sentence){
				$description_result = $full_descr_sentence;
			}
			
			if(!$full_descr_sentence && $breakup_descr_sentence){
				$description_result = $breakup_descr_sentence;
			}
			
			$title_result = isset($title_result) ? $title_result : '';
			$description_result = isset($description_result) ? $description_result : '';
			
			if($title_result && $description_result){
				// assign the $description_result to $title_result
				$k = count($title_result);
				for($j=0;$j<count($description_result);$j++){
					$title_result[$k] = $description_result[$j];
					$k++;
				}
				$fin_result = array_unique($title_result, SORT_REGULAR);
				$fin_result = array_values($fin_result);
			}
			
			if($title_result && !$description_result){
				$fin_result = $title_result;
			}

			if(!$title_result && $description_result){
				$fin_result = $description_result;
			}
			
			$fin_result = isset($fin_result) ? $fin_result : '';
			
			if($fin_result){
				$result_handle = '';
				// pagination begin
				$page = isset($_GET['page']) ? $_GET['page'] : '';
				$page = intval($page);
				if($page == '' || $page<=0) $page = 1;
				$num_items = count($fin_result);
				$items_per_page = 2;
				$num_pages = ceil($num_items/$items_per_page);
				if(($page>$num_pages) && $page !=1 )$page = $num_pages;
				$start = ($page-1) * $items_per_page;
				$end = $start + $items_per_page;
				// pagination end
				
				for($i=$start;$i<$end;$i++){
					if(isset($fin_result[$i]['id']))
						$result_handle .= intval($fin_result[$i]['id']).',';
				}
				$con = null;
				// // remove the last comma
				$id_list = rtrim($result_handle,',');
				$posts = $db->prepare("SELECT id, title, short_description 
				FROM posts WHERE id IN ($id_list) ORDER BY id DESC");
				$posts->execute();
				while($post = $posts->fetch(PDO::FETCH_ASSOC)){
						$con .= '
							<div class="article">
							<strong>'.$post['title'].'</strong><br />
							'.htmlspecialchars_decode($post['short_description']).'
							</div>';
				}
				$con .= "<div class='article' style='text-align:center;'>
					<small>Page <b>$page</b> from <b>$num_pages</b></small><br/>";
				if($page>1)
				{
				  $ppage = $page-1;
				  $con .= "<small><a href=\"?sentence=$text&amp;page=$ppage\">
				  &#171; Previous Page</a> | </small>";
				}
				if($page<$num_pages)
				{
				  $npage = $page+1;
				  $con .= "<small><a href=\"?sentence=$text&amp;page=$npage\">
				  Next Page &#187;</a></small>";
				}
				$con .= "</div>";
			}else{
				$con = '<div class="article">Not found!</div>';
			}
		}else{
			$con = '<div class="article">Not found!</div>';
		}
		return $con;
	}

}
?>