/**
 * @author adminuser
 *
 * Requires jQuery
 */
function CwrcEntity(type, url, jq) {
	if (!jq) {
		jq = $;
	}

	// Public Functions
	this.getEntity = function(pid) {
		var result = result;

		jq.ajax({
			url : url + '/' + type + "/" + pid,
			type : 'GET',
			async : false,
			success : function(data) {
				result = data;
			},
			error : function(error) {
				result = error;
			}
		});

		return result;
	}

	this.newEntity = function(data) {
		var result = result;

		jq.ajax({
			url : url + '/' + type,
			type : 'POST',
			data : {
				method : 'post',
				data: data
			},
			async : false,
			success : function(data) {
				result = data;
			},
			error : function(error) {
				result = error;
			}
		});

		return jq.parseJSON(result);
	}

	this.modifyEntity = function(pid, data) {

	}

	this.deleteEntity = function(pid) {

	}
	
	this.listEntity = function(totalPerPage, page){
		alert("Page " + page);
	}
}

function CwrcApi(url, jq) {
	if (!jq) {
		jq = $;
	}

	// Class creation
	if (!url.indexOf("/", this.length - 1) !== -1) {
		url = url + "/";
	}
	// Private variables
	var _this = this;

	// Public variables
	this.person = new CwrcEntity('person', url, jq);
	this.organization = new CwrcEntity('organization', url, jq);
	this.title = new CwrcEntity('title', url, jq);
	//this.event = new CwrcEntity('event', url, jq); <- Can we use event or is that a javascript keyword?
	this.place = new CwrcEntity('place', url, jq);

	// Private functions

	// Public functions
	this.isInitialized = function() {
		var prefix = "cwrc-api=";
		var dc = document.cookie;
		var index = dc.indexOf("; " + prefix);
		if (index == -1) {
			index = dc.indexOf(prefix);
			return index == 0;
		}

		return true;
	}

	this.initializeWithCookie = function(name) {
		var result = result;

		if (!_this.isInitialized()) {
			jq.ajax({
				url : url + "initialize_user",
				type : 'POST',
				async : false,
				data : {
					name : name
				},
				success : function(data) {
					result = data;
				},
				error : function(error) {
					result = error;
				}
			});
		}

		return result;
	}

	this.initializeWithLogin = function(username, password) {
		var result = result;

		if (!_this.isInitialized()) {
			jq.ajax({
				url : url + "initialize_user",
				type : 'POST',
				async : false,
				data : {
					username : username,
					password : password
				},
				success : function(data) {
					result = data;
				},
				error : function(error) {
					result = error;
				}
			});
		}

		return result;
	}

	this.logout = function() {
		alert("Logout not yet implemented.");
	}

	return this;
}
