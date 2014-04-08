<?php
getRoute()->get('/place/search', array('PlaceController', 'search'));
getRoute()->get('/place/(.+)', array('PlaceController', 'view'));
getRoute()->post('/place', array('PlaceController', 'create'));
getRoute()->post('/place/(.+)', array('PlaceController', 'create'));
getRoute()->put('/place/(.+)', array('PlaceController', 'update'));
getRoute()->delete('/place/(.+)', array('PlaceController', 'delete'));
include_once './controllers/entity.php';

class PlaceController extends EntityController {
	
	private static function getLabel($data){
		// This assumes that the place object is properly formed.
		
		$xmlObj = simplexml_load_string($data);
		$nameParts = $xmlObj->place[0]->identity[0]->preferredForm[0]->namePart;
		
        
        return (string)$nameParts; 
       
        
	}
	
	public static function search(){
		$query = $_GET['query'];
		$limit = $_GET['limit'];
		$page = $_GET['page'];
		
		$result = EntityController::searchEntities("cwrc:place-entityCModel", $query, $limit, $page);
		
		echo($result);
	}
	
	public static function view($id){
		$result = EntityController::getEntity($id, 'PLACE');
		
		if(get_class($result) == "Entity"){
			echo $result->getContent();
		}else{
			echo $result;
		}
	}
	
	public static function createNew($data){
		$result = EntityController::uploadNewEntity('cwrc', 'PLACE', $data['data'], static::getLabel($data['data']), "cwrc:place-entityCModel", "cwrc:20d46869-20a0-4d19-9ef6-1aacb3a1fba8");
		$object = array();
		
		if(get_class($result) == "Entity"){
			$object["pid"] = $result->getPID();
		}else{
			$object["error"] = $result;
		}
		
		echo json_encode($object);
	}
	
	public static function update($id){
		$result = EntityController::modifyEntity('PLACE', $id, $_POST['data'], static::getlabel($_POST['data']));
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