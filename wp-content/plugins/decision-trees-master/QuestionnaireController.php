<?php 
class QuestionnaireController
{
	private $DecisionTree;
	private $Answer;
	function __construct($DecisionTree = null,$Answer = null)
	{
		if (!is_null($DecisionTree)){
			$this->DecisionTree = $DecisionTree;
		}
		if (!is_null($Answer)){
			$this->Answer = $Answer;
		}
	}

	public function saveSelectableQuestions()
	{
		$postIDs = explode(',',$_POST['id']);
		$bbb = $_SESSION['questionnaire_tree'];
		foreach ($postIDs  as $singlePostID){
			$result = $this->_changeQuestionnaireValue($singlePostID,array('selected'=>true), $bbb);
		}
		$_SESSION['questionnaire_tree'] = $bbb;
		update_user_meta($_SESSION['client_id'],'questionnaire_tree',$_SESSION['questionnaire_tree']);
		echo json_encode(array('result'=>'success','value'=>$this->getNextPageUrl($_SESSION['questionnaire_tree'], 'selectable')));
	}
	public function saveQuestionValue()
	{
		$bbb = $_SESSION['questionnaire_tree'];
		$result = $this->_changeQuestionnaireValue($_POST['current-post-id'], array('value'=>$_POST['value']), $bbb);
		$_SESSION['questionnaire_tree'] = $bbb;
		update_user_meta($_SESSION['client_id'],'questionnaire_tree',$_SESSION['questionnaire_tree']);
		;
		echo json_encode(array('result'=>'success','value'=>$this->getNextSiblingUrl($_POST['current-post-id'],$_SESSION['questionnaire_tree'])));

	}

	public function saveQuestionValues($clientID, $postID, $questionnaireTree, $params)
	{
		$result = $this->_changeQuestionnaireValue($postID, $params, $questionnaireTree);
		return update_user_meta($clientID,'questionnaire_tree',$questionnaireTree);

	}

	public function changeQuestionnaireValue($postID, $params)
	{
		$bbb = $_SESSION['questionnaire_tree'];
		$result = $this->_changeQuestionnaireValue($postID, $params, $bbb);
		$_SESSION['questionnaire_tree'] = $bbb;
		return update_user_meta($_SESSION['client_id'],'questionnaire_tree',$_SESSION['questionnaire_tree']);
	}

	public function skipQuestionValue()
	{
		$bbb = $_SESSION['questionnaire_tree'];
		$result = $this->_changeQuestionnaireValue($_POST['current-post-id'], array('value'=>null, 'skip'=>true), $bbb);
		$_SESSION['questionnaire_tree'] = $bbb;
		update_user_meta($_SESSION['client_id'],'questionnaire_tree',$_SESSION['questionnaire_tree']);
		;
		echo json_encode(array('result'=>'success','value'=>$this->getNextSiblingUrl($_POST['current-post-id'],$_SESSION['questionnaire_tree'])));

	}

	public function ignoreQuestionValue()
	{
		$bbb = $_SESSION['questionnaire_tree'];
		$result = $this->_changeQuestionnaireValue($_POST['current-post-id'], array('value'=>null, 'ignore'=>true), $bbb);
		$_SESSION['questionnaire_tree'] = $bbb;
		update_user_meta($_SESSION['client_id'],'questionnaire_tree',$_SESSION['questionnaire_tree']);
		;
		echo json_encode(array('result'=>'success','value'=>$this->getNextSiblingUrl($_POST['current-post-id'],$_SESSION['questionnaire_tree'])));

	}

	public function initializeQuestinonnaireTree($clientID, $forcePopulate = false)
	{
		$currentUser = $this->DecisionTree->get_user_with_meta($clientID);
		if (isset($currentUser->meta_data['questionnaire_tree'][0]) && $forcePopulate == false){
			$_SESSION['questionnaire_tree'] = unserialize($currentUser->meta_data['questionnaire_tree'][0]);
		}
		else{
			$_SESSION['questionnaire_tree'] = $this->populateQuestinonnaireTree($clientID);
			update_user_meta($clientID,'questionnaire_tree',$_SESSION['questionnaire_tree']);
		} 
	}

	public function getNextSequenceUrl()
	{
		$this->getNextPageUrl($_SESSION['questionnaire_tree'], 'sequence','sibling');
	}

	public function getCurrentPostUserData($postID,$questionnaireTree = null)
	{
		return $this->_getCurrentPostUserData($postID, (($questionnaireTree == null)?$_SESSION['questionnaire_tree']:$questionnaireTree));
	}

	public function getAnsweredQuestions($questionnaireTree)
	{
		$returnValue = array();
		$this->_getAnsweredQuestions($questionnaireTree, $returnValue);
		return $returnValue;
	}
	public function getUnAnsweredQuestions($questionnaireTree)
	{
		$returnValue = array();
		$this->_getUnAnsweredQuestions($questionnaireTree, $returnValue);
		return $returnValue;
	}
	public function getAnsweredQuestionsWithParent($questionnaireTree)
	{
		$returnValue = array();
		$this->_getAnsweredQuestionsWithParent($questionnaireTree, $returnValue, '');
		return $returnValue;
	}

	private function populateQuestinonnaireTree($clientID)
	{
		$post_status = get_post_stati();
		unset(
			$post_status['trash'],
			$post_status['auto-draft'],
			$post_status['inherit']
		);
		$questionnaireTree = array();
		$tree = get_pages( array(
			'post_type'   => $this->DecisionTree->post_type,
			'post_status' => $post_status,
			'sort_column' => 'menu_order,post_title',
			'parent'      => 0,
		) );
		foreach($tree as $key => $singleTree){
			$questionnaireTree = $this->DecisionTree->populate_tree( $tree, $key);
		};
		$this->reduceQuestionnaireTree($tree, $returnArray);
		return $returnArray;
	}

	private function reduceQuestionnaireTree($tree, &$returnArray)
	{
		foreach($tree as $key => $singlePost){
			$returnArray[$key] = array(
				'ID'=>$singlePost->ID,
				'post_parent'=>$singlePost->post_parent,
				'post_title'=>$singlePost->post_title,
				'guid'=>$singlePost->guid,
				'price'=>isset($singlePost->metadata['Price'][0]) ? $singlePost->metadata['Price'][0] : null,
				'sequence'=>isset($singlePost->metadata['sequence'][0]) ? $singlePost->metadata['sequence'][0] : null,
				'selectable'=>isset($singlePost->metadata['selectable'][0]) ? $singlePost->metadata['selectable'][0] : null,
				'value'=>isset($singlePost->metadata['value'][0]) ? $singlePost->metadata['value'][0] : null,
				'required'=>isset($singlePost->metadata['required'][0]) ? $singlePost->metadata['required'][0] : null,
				'visited'=>false,
			);
			if (isset($singlePost->children))
				$this->reduceQuestionnaireTree( $singlePost->children, $returnArray[$key]['children'] );
		}
	}

	private function _changeQuestionnaireValue($postID, $params, &$questionnaireTree)
	{
		foreach($questionnaireTree as $postKey => $singleQuestion){
			if ($singleQuestion['ID']==$postID){
				foreach($params as $key => $value){
					$questionnaireTree[$postKey][$key] = $value;
				}
				return true;
			}
			if (isset($singleQuestion['children'])){
				$this->_changeQuestionnaireValue($postID, $params, $questionnaireTree[$postKey]['children']);
			}
		}
		return false;
	}
	/*
	$levelType : sibling - search on the same level
	$levelType : children - search only in children
	$levelType : none 	  - whole tree

	*/
	private function getNextPageUrl($questionnaireTree, $questionType, $levelType = null)
	{
		foreach($questionnaireTree as $postKey => $singleQuestion){
			if ($singleQuestion[$questionType]=='1' && $singleQuestion['visited'] == false){
				return $singleQuestion['guid'];
			}
			else if (isset($singleQuestion['children'])){
				$returnValue = $this->getNextPageUrl($questionnaireTree[$postKey]['children'], $questionType);
				if ($returnValue != false)
					return $returnValue;
			}
		}
		return false;
	}
	/*
	$levelType : sibling - search on the same level
	$levelType : children - search only in children
	$levelType : none 	  - whole tree

	*/
	private function getNextSiblingUrl($postID, $questionnaireTree)
	{
		$currentLevel = false;
		$parentLevel = false;
		$nextParentSiblingUrl = false;
		foreach($questionnaireTree as $postKey => $singleQuestion){
			if ($singleQuestion['ID']==$postID){
				$currentLevel = true;
			}
			else if ($currentLevel==true){
				return $singleQuestion['guid'];
			}
		}
		if ($currentLevel == false){
			foreach($questionnaireTree as $postKey => $singleQuestion){
				if (isset($singleQuestion['children'])){
					$returnedValue = $this->getNextSiblingUrl($postID, $questionnaireTree[$postKey]['children']);
					if ($parentLevel == true){
						return $singleQuestion['guid'];
					}
					if ($returnedValue === -2){
						$parentLevel = true;
					}
					else if ($returnedValue != false){
						return $returnedValue;
					}
				}
			}
			if ($parentLevel == true){
				return -2;
			}
		}
		else if($currentLevel == true){
			return -2;
		}
		return false;
	}

	private function _getAnsweredQuestions($tree, &$returnArray)
	{
		foreach($tree as $key => $singlePost){
			if (isset($singlePost['value']) && strlen($singlePost['value']>0)){
				$returnArray[] = $singlePost;
			}
			if (isset($singlePost['children']))
				$this->_getAnsweredQuestions( $singlePost['children'], $returnArray);
		}
	}

	private function _getUnAnsweredQuestions($tree, &$returnArray)
	{
		foreach($tree as $key => $singlePost){
			if (isset($singlePost['value']) && strlen($singlePost['value']==0) && !(isset($singlePost['ignore']) && $singlePost['ignore']==1)){
				$returnArray[] = $singlePost;
			}
			if (isset($singlePost['children']))
				$this->_getUnAnsweredQuestions( $singlePost['children'], $returnArray);
		}
	}

	private function _getAnsweredQuestionsWithParent($tree, &$returnArray, $title)
	{
		foreach($tree as $key => $singlePost){
			if (isset($singlePost['value']) && strlen($singlePost['value']>0)){
				$singlePost['post_title'] = $title . ' > ' . $singlePost['post_title'];
				$singlePost['post_title'] = substr($singlePost['post_title'],3);
				$returnArray[] = $singlePost;
			}
			if (isset($singlePost['children'])){
				$this->_getAnsweredQuestionsWithParent( $singlePost['children'], $returnArray, $title . ' > ' . $singlePost['post_title']);
			}
		}
	}

	private function _getCurrentPostUserData($postID, $questionnaireTree)
	{
		foreach($questionnaireTree as $postKey => $singleQuestion){
			if ($singleQuestion['ID']==$postID){
				return $singleQuestion;
			}
			if (isset($singleQuestion['children'])){
				$returnValue = $this->_getCurrentPostUserData($postID, $questionnaireTree[$postKey]['children']);
				if ($returnValue != false)
					return $returnValue;
			}
		}
		return false;
	}
}