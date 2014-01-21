<?php
getRoute()->get('/title/search', array('TitleController', 'search'));
getRoute()->get('/title/(.+)', array('TitleController', 'view'));
getRoute()->post('/title', array('TitleController', 'create'));
getRoute()->post('/title/(.+)', array('TitleController', 'create'));
getRoute()->put('/title/(.+)', array('TitleController', 'update'));
getRoute()->delete('/title/(.+)', array('TitleController', 'delete'));
include_once './controllers/entity.php';

class TitleController extends EntityController {
	
	private static function getLabel($data){
		// This assumes that the title object is properly formed.
		
		$xmlObj = simplexml_load_string($data);
		$nameParts = $xmlObj->title[0]->identity[0]->preferredForm[0]->namePart;
		
		return $nameParts;
	}
	
	public static function search(){
		$query = $_GET['query'];
		$limit = $_GET['limit'];
		$page = $_GET['page'];
		
		$result = EntityController::searchEntities('MODS', $query, $limit, $page);
		
		echo($result);
	}
	
	public static function view($id){
		$result = EntityController::getEntity($id, 'MODS');
		
		if(get_class($result) == "Entity"){
			echo $result->getContent();
		}else{
			echo $result;
		}
	}
	
	public static function createNew($data){
		$result = EntityController::uploadNewEntity('cwrc', 'MODS', $data['data'], static::getLabel($data['data']));
		$object = array();
		
		if(get_class($result) == "Entity"){
			$object["pid"] = $result->getPID();
		}else{
			$object["error"] = $result;
		}
		
		echo json_encode($object);
	}
	
	public static function update($id){
		$result = EntityController::modifyEntity('MODS', $id, $_POST['data']);
		$object = array();
		
		if(get_class($result) == "Entity"){
			$object["pid"] = $result->getPID();
		}else{
			$object["pid"] = $id;
			$object["error"] = $result;
		}
		
		echo json_encode($object);
	}
	
	public static function delete($id){
		$result = EntityController::deleteEntity($id);
		$object = array();
		
		if($result == null){
			$object['isDeleted'] = true;
		}else{
			$object['isDeleted'] = false;
			$object['error'] = $result;
		}
		
		echo json_encode($object);
	}
}

?>