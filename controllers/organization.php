<?php
getRoute()->get('/organization/search', array('OrganizationController', 'search'));
getRoute()->get('/organization/(.+)', array('OrganizationController', 'view'));
getRoute()->post('/organization', array('OrganizationController', 'create'));
getRoute()->post('/organization/(.+)', array('OrganizationController', 'create'));
getRoute()->put('/organization/(.+)', array('OrganizationController', 'update'));
getRoute()->delete('/organization/(.+)', array('OrganizationController', 'delete'));
include_once './controllers/entity.php';

class OrganizationController extends EntityController {
	
	private static function getLabel($data){
		// This assumes that the organization object is properly formed.
		
		$xmlObj = simplexml_load_string($data);
		$nameParts = $xmlObj->organization[0]->identity[0]->preferredForm[0]->namePart;
		
		return (string)$nameParts;              
    }


	
	public static function search(){
		$query = $_GET['query'];
		$limit = $_GET['limit'];
		$page = $_GET['page'];
		
		$result = EntityController::searchEntities('ORGANIZATION', $query, $limit, $page);
		
		echo($result);
	}
	
	public static function view($id){
		$result = EntityController::getEntity($id, 'ORGANIZATION');
		
		if(get_class($result) == "Entity"){
			echo $result->getContent();
		}else{
			echo $result;
		}
	}
	
	public static function createNew($data){
		$result = EntityController::uploadNewEntity('cwrc', 'ORGANIZATION', $data['data'], static::getLabel($data['data']));
		$object = array();
		
		if(get_class($result) == "Entity"){
			$object["pid"] = $result->getPID();
		}else{
			$object["error"] = $result;
		}
		
		echo json_encode($object);
	}
	
	public static function update($id){
		$result = EntityController::modifyEntity('ORGANIZATION', $id, $_POST['data']);
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
