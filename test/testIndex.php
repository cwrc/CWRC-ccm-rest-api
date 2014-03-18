<?php
getRoute()->get('/tests', array('Tests', 'home'));
getRoute()->get('/tests/addEntity', array('Tests', 'addEntity'));
getRoute()->get('/tests/viewEntity/(.+)/(.+)', array('Tests', 'viewEntity'));
getRoute()->get('/tests/listEntities', array('Tests', 'listEntities'));

class Tests{
	public static function show_login(){
		echo "<script src='" . cwrc_site() . "/scripts/jquery-1.10.2.min.js'></script>";
		echo "<script src='" . cwrc_site() . "/scripts/cwrc-api.js'></script>";
		echo "<script type='text/javascript'>";
		echo "var cwrcApi = new CwrcApi('" . cwrc_site() . "', $)";
		echo "</script>";
		
		if(count(get_login_cookie()) > 0){
			//echo "<h4>Logged in as " . $_SESSION['username'] . "</h4>";
			
			echo "<button onclick='cwrcApi.logout();location.reload();' value='Logout'>Logout</button>";
			echo "</form></br>";
		}else{
			echo "<p>Please login:</p>";
			echo "Username: <input type='text' id='loginUsername' name='username'/>";
			echo "Password: <input type='password' id='loginPassword' name='password'/>";
			echo "<button onclick='cwrcApi.initializeWithLogin($(\"#loginUsername\").val(), $(\"#loginPassword\").val());location.reload();'>Login</button>";
			echo "</br>";
		}
		
		/*foreach ($_SERVER as $key => $value) {
			echo "<p>";
			echo $key . " == " . $value;
			echo "</p>";
		}*/
	}
	
	/*public static function logout(){
		cwrc_logout();
		
		$_SESSION['username'] = null;
		header('location: ' . $_SERVER['HTTP_REFERER']);
	}
	
	public static function login(){
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		// Run login function
		cwrc_login($username, $password);
		
		$_SESSION['username'] = $username;
		header('location: ' . $_SERVER['HTTP_REFERER']);
	}*/
	
	public static function home(){
		self::show_login();
		echo "<h1>Cwrc API Tests</h1>";
		echo "<ul>";
		echo "<li><a href='" . cwrc_site() . "/tests/listEntities'>List Entities</a></li>";
		echo "<li><a href='" . cwrc_site() . "/tests/addEntity'>Add Entity</a></li>";
		echo "</ul>";
	}
	
	public static function viewEntity($type, $pid){
		self::show_login();
		$entity = EntityController::getEntity($pid, $type);
		
		echo "<h1>View Entity</h1>";
		
		echo "<h2>Type: " . htmlspecialchars($type) . "</h2>";
		echo "<h2>PID: " . htmlspecialchars($pid) . "</h2>";
		echo "<h2>Label: " . htmlspecialchars($entity->getLabel()) . "</h2>";
		
		echo "<h2>Content</h2>";
		echo "<textarea id='entityContent' name='data'></textarea>";
		echo "<div>";
		echo "<button onclick='return updateEntity();'>Update</button>";
		echo "<button onclick='deleteEntity();'>Delete</button>";
		echo "</div>";
		
		echo "<script type='text/javascript'>
			function deleteEntity(){
				if(confirm('Are you sure you wish to delete this entity?')){
					var result = cwrcApi['" . $type . "'].deleteEntity('" . $pid . "');
					
					if(result.isDeleted){
						alert('Entity Successfully deleted.');
					}else{
						alert(result.error);
					}
				}	
			}
			
			function updateEntity(){
				var result = cwrcApi['" . $type . "'].modifyEntity('" . $pid . "', $('#entityContent').val());
				
				if(result.error){
					alert(result.error);
				}else{
					alert('Entity modified successfully.')
				}
				
				return false;
			}
			
			$(document).ready(function(){
				var val = cwrcApi['" . $type . "'].getEntity('" . $pid . "');
				$('#entityContent').val(val);
			});
		</script>";
	}
	
	public static function listEntities(){
		self::show_login();
		echo "<h1>List Entities</h1>";
		
		echo "<h2>Type:</h2>";
			
		
        echo "<select id='entityType' >";
        echo "<option></option>";
        echo "<option value='person'>Person</option>";
        echo "<option value='place'>Place</option>";
        echo "<option value='organization'>Organization</option>";
        echo "<option value='title'>Title</option>";
        echo "</select>";
		
		
		echo "<div>Search:&nbsp<input id='searchText'/></div>";
		echo "<button onclick='search()'>Search</button>";
		
		echo "<br/>";
		
		echo "<h2>Entities</h2>";
		echo "<table>";
		echo "<thead><tr>
			<th>PID</th>
			<th>Label</th>
			<th>Action</th>
		</tr></thead>";
		echo "<tbody id='table_body'></tbody>";
		echo "</table>";
		
		echo "<script type='text/javascript'>
			function errorResult(result){
				alert(result);
			}
			
			function searchResult(result){
				var searchText = $('#searchText').val();
				var entity = $('#entityType').val();
				var key;
				
				$('#table_body').empty();
				
				if(!result.response){
					alert(result);
				}
				
				var objects = result.response.objects;
				
				for(key in objects){
					var object = objects[key];
					var row = $('<tr></tr>');
					
					var data = $('<td></td>');
					data.text(object.PID);
					row.append(data);
					
					var data = $('<td></td>');
					if(object.solr_doc['dc.title']){
						data.text(object.solr_doc['fgs.label'][0]);
					}
					row.append(data);
					
					var data = $('<td></td>');
					data.append('<a href=\"" . cwrc_site() . "/tests/viewEntity/' + entity + '/' + encodeURIComponent(object.PID) + '\">View</a>');
					row.append(data);
					
					$('#table_body').append(row);
				}
			}
			
			function search(){
				var searchText = $('#searchText').val();
				var entity = $('#entityType').val();
				var key;
				
                
				var result = cwrcApi[entity].searchEntity({
					query: searchText, 
					success: searchResult,
					error: errorResult,
					limit: 100,
					page: 0
				});
			}
		</script>";
	}
	
	public static function addEntity(){
	    
		$examplePerson = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<entity>
			<person>
				<recordInfo>
                    <originInfo>
                                <projectId>record for testing API</projectId>
                    </originInfo>
                </recordInfo>
				<identity>
					<preferredForm>
						<namePart>Test Person</namePart>
					</preferredForm>
				</identity>
				<description>
				</description>
			</person>
		</entity>";
		$examplePerson = json_encode($examplePerson);//htmlspecialchars($examplePerson, ENT_QUOTES, ISO-8859-1, false);
        
        $examplePlace = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <entity>
            <place>
                <recordInfo>
                    <originInfo>
                                <projectId>record for testing API</projectId>
                    </originInfo>
                </recordInfo>
                <identity>
                    <preferredForm>
                        <namePart>Test Place</namePart>
                    </preferredForm>
                </identity>
                <description>
                </description>
            </place>
        </entity>";
        $examplePlace = json_encode($examplePlace);//htmlspecialchars($examplePlace, ENT_QUOTES, ISO-8859-1, false);
        
        
        $exampleOrganization = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <entity>
            <organization>
                <recordInfo>
                    <originInfo>
                                <projectId>record for testing API</projectId>
                    </originInfo>
                </recordInfo>
                <identity>
                    <preferredForm>
                        <namePart>Test Organization</namePart>
                    </preferredForm>
                </identity>
                <description>
                </description>
            </organization>
        </entity>";
        $exampleOrganization = json_encode($exampleOrganization);//htmlspecialchars($exampleOrganization, ENT_QUOTES, ISO-8859-1, false);
        
        $exampleTitle = "<mods>
        <titleInfo>
            <title>the titles tests</title>
        </titleInfo>
        <recordInfo>
        </recordInfo>
        </mods>";
        $exampleTitle = json_encode($exampleTitle);//htmlspecialchars($exampleTitle, ENT_QUOTES, ISO-8859-1, false);
        
        
        $noSelection = "Select type for appropriate XML template";
        $noSelection = htmlspecialchars($noSelection, ENT_QUOTES, ISO-8859-1, false);
		
		self::show_login();
		
		echo "<h1>Add Entity</h1>";
		
		echo "<div>";
		echo "<span class='label'>Entity Type</span>";
		echo "<select id='entityType'onChange='updateText();' >";
        echo "<option value='select type'>selecttype</option>";
		echo "<option value='person'>Person</option>";
        echo "<option value='place'>Place</option>";
        echo "<option value='organization'>Organization</option>";
        echo "<option value='title'>Title</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div>";
		echo "<textarea id='entityData' name='data'>" . $noSelection  . "</textarea>";
		echo "</div>";
		
		echo "<button onclick='submitEntity();'>Submit</button>";
		
		echo "<script type='text/javascript'>
		
		     
		     function updateText(){
                 
                 var cwrctypemobj = document.getElementById('entityType');
                 var cwrctypetext = cwrctypemobj.options[cwrctypemobj.selectedIndex].text;
                
                 //document.getElementById('entityData').value = cwrctypetext;
                 //document.getElementById('entityData').value = 'blah';
                 
             switch (cwrctypetext)
                 {
                   case 'Person':
                   document.getElementById('entityData').value = " .$examplePerson . ";
                   break;
                   
                   case 'Place':
                   document.getElementById('entityData').value = " .$examplePlace . ";
                   break;
                   
                   case 'Organization':
                   document.getElementById('entityData').value = " .$exampleOrganization . ";
                   break;
                   
                   case 'Title':
                   document.getElementById('entityData').value = " .$exampleTitle . ";
                   break;
                         
                 }             


             
             }
             
		
			function submitEntity(){
				var type = $('#entityType').val();
				var val = $('#entityData').val();
                
				
				var result = cwrcApi[type].newEntity(val);
			
				if(result.error){
					alert(result.error);
				}else{
					window.location.href = '" . cwrc_site() . "/tests/viewEntity/' + encodeURIComponent(type) + '/' + encodeURIComponent(result.pid);
				}
			}
		</script>";
	}
	
	private static function addAnnotationComponent(){
	    
		$exampleDate = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<entity>
			<person>
				<recordInfo>
                    <originInfo>
                                <projectId>record for testing API</projectId>
                    </originInfo>
                </recordInfo>
				<identity>
					<preferredForm>
						<namePart>Test Person</namePart>
					</preferredForm>
				</identity>
				<description>
				</description>
			</person>
		</entity>";
		$exampleDate = json_encode($exampleDate);//htmlspecialchars($examplePerson, ENT_QUOTES, ISO-8859-1, false);
        
        $noSelection = "Select type for appropriate XML template";
        $noSelection = htmlspecialchars($noSelection, ENT_QUOTES, ISO-8859-1, false);
		
		echo "<div class='sideComponent'>";
		
		echo "<h2>Add Annotation</h2>";
		
		echo "<div>";
		echo "<span class='label'>Annotation Type</span>";
		echo "<select id='annotationType' onChange='updateAnnotationText();' >";
        echo "<option value='select type'>selecttype</option>";
		echo "<option value='date'>Date</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div>";
		echo "<textarea id='annotationData' name='data'>" . $noSelection  . "</textarea>";
		echo "</div>";
		
		echo "<button onclick='submitAnnotation();'>Submit</button>";
		
		echo "<script type='text/javascript'>
		
		     
		     function updateAnnotationText(){
                 
                 var cwrctypemobj = $('#annotationType');
                 var cwrctypetext = cwrctypemobj.find(':selected'').val();
                 
             	switch (cwrctypetext)
                 {
                   case 'date':
                   document.getElementById('annotationData').value = " . $exampleDate . ";
                   break;
                 }   
             }
             
		
			function submitAnnotation(){
				var type = $('#annotationType').val();
				var val = $('#annotationData').val();
                
				
				var result = cwrcApi['annotation'].newAnnotation(val);
			
				if(result.error){
					alert(result.error);
				}else{
					window.location.href = '" . cwrc_site() . "/tests/viewEntity/' + encodeURIComponent(type) + '/' + encodeURIComponent(result.pid);
				}
			}
		</script>";
		echo "</div>";
	}
}
