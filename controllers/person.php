<?php
getRoute()->get('/person/search', array('PersonController', 'search'));
getRoute()->get('/person/(.+)', array('PersonController', 'view'));
getRoute()->post('/person', array('PersonController', 'create'));
getRoute()->post('/person/(.+)', array('PersonController', 'create'));
getRoute()->put('/person/(.+)', array('PersonController', 'update'));
getRoute()->delete('/person/(.+)', array('PersonController', 'delete'));
include_once './controllers/entity.php';

class PersonController extends EntityController {
	const PERSON = "PERSON";
	const MODEL = "cwrc:person-entityCModel";
	
	private static function getLabel($data){
		// This assumes that the person object is properly formed.
		
		$xmlObj = simplexml_load_string($data);
		$nameParts = $xmlObj->person[0]->identity[0]->preferredForm[0]->namePart;
		$surname = "";
		$forename = "";
		
		foreach($nameParts as $namePart){
			if(strcmp($namePart['partType'], "surname") == 0){
				$surname = $surname . " " . $namePart;
			}else if(strcmp($namePart['partType'], "forename") == 0){
				$forename = $forename . " " . $namePart;
			}else{
				$surname = $surname . " " . $namePart;
			}
		}
		
		trim($surname);
		trim($forename);
		
		if(strlen($forename) > 0){
			if(strlen($surname) > 0){
				return $surname . ", " . $forename;
			}else{
				return $forename;
			}
		}
		
		return $surname;
	}
	
	public static function search(){
		$query = $_GET['query'];
		$limit = $_GET['limit'];
		$page = $_GET['page'];
		
		$result = EntityController::searchEntities("info:fedora/" . self::MODEL, $query, $limit, $page);

		echo($result);
	}
	
	public static function view($id){
		$result = EntityController::getEntity($id, self::PERSON);
		
		if(get_class($result) == self::ENTITY){
			echo $result->getContent();
		}else{
			echo $result;
		}
	}
	
	public static function createNew($data){
		$result = EntityController::uploadNewEntity(self::API_NAMESPACE, self::PERSON, $data['data'], static::getLabel($data['data']), self::MODEL, "cwrc:d3c004f1-2b3f-4e51-a679-4f1c0da4fe17");
		$object = array();
		
		if(get_class($result) == self::ENTITY){
			$object["pid"] = $result->getPID();
		}else{
			$object["error"] = $result;
		}
		
		echo json_encode($object);
	}
	
	public static function update($id){
		$result = EntityController::modifyEntity(self::PERSON, $id, $_POST['data'], static::getlabel($_POST['data']));
		$object = array();
		
		if(get_class($result) == self::ENTITY){
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