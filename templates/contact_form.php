<form id="contactForm" class="form-horizontal">
	<legend>Contact Info</legend>
</form>
<div class="row contact-form-header">
	<div class="span2">Contact Date</div>
	<div class="span2">Type</div>
	<div class="span6">Summary</div>
	<div class="span2">Added by</div>
</div>
<div class='row contact-form-new' onclick="newContact()" data-title="Add New Contact" data-content="<?php echo random_quote()?>">Add New</div>
<div id="PutContactsHere">
</div>
<br />
<br />
<script>
	// contacts as JSON from server
	var contacts = <?php echo json_encode($contacts) ?>; 

	// global variables needed to run
	var state = {
		newContactShown : false,  
	}
	
	// given a contactID, get the contact
	function getContact(id) {
		for(n in contacts) {
			if(contacts[n].ContactID == id) {
				return contacts[n]
			}
		}		
		return null; 
	}

	// displays the html for each contact
	function contactDisplayHTML(contact) {
		var id = contact.ContactID; 

		var html =  
			"<div id='Contact"+ id +"' class='row contact-form-row'"
					+ "onclick='showEdit("+id+")' " 
					+ "data-title='Last Edit' "
					+ "data-content='" + contact.UserName.Edit
					+ " on " + contact.ContactEditDate + "'>"  
				+ "<div class='span2 divContactDate'>"
					+ "<span id='Contact"+ id +"Date'>" 
						+ contact.ContactDate + "</span>" 
				+ "</div>"
				+ "<div class='span2 divContactType'>"
					+ "<span id='Contact"+ id + "Type'>" 
						+ contact.ContactType + "</span>"
				+ "</div>"
				+ "<div class='span6 contact-form-summary divContactSummary'>" 
					+ "<span id='Contact" +id+"Summary'>"
						+ contact.ContactSummary + "</span>" 
				+ "</div>"
				+ "<div class='span2'>"
					+ contact.UserName.Added
				+ "</div>"
			+ "</div><br />";
		
		return html; 
	}
	
	/* NOTE: you must manually select ContactType */
	function contactEditHTML(contact) {
		var isNew = (arguments.length < 1);
		
		var id = (isNew ? 0 : contact.ContactID); 
		var ContactDate = (isNew ? currentSqlDate() : contact.ContactDate); 
		var ContactSummary = (isNew ? "" : contact.ContactSummary); 
		
		var html =  
		"<form id='EditContact" + id + "' class='contact-edit-form form-horizontal' hidden>" 
			+ "<div class='row contact-edit-row'>"
				+ "<div class='row'>" 
					+ "<div class='span6'>"
						+ "<div class='control-group'>"
							+ "<label class='control-label'>Date: </label>"
							+ "<div class='controls'>"
								+ "<input type='text' name='ContactDate' value='" + ContactDate + "' data-title='Invalid' />" 
							+ "</div>"
						+ "</div>"
						+ "<div class='control-group'>"
							+ "<label class='control-label'>Type: </label>"
							+ "<div class='controls'>"
								+ "<select name='ContactType' >"
									+ "<?php echo htmlOptions($contact_types) ?>"
								+ "</select>"
							+ "</div>"
						+ "</div>"
					+ "</div>"
					+ "<div class='span6'>"
						+ "<textarea class='field span5' rows='6' name='ContactSummary'>"+ContactSummary+"</textarea>"
					+ "</div>"
				+ "</div>"
				+ "<br />"
				+ "<div class='row'>"
					+ "<div class='span2'></div>"
					+ "<div class='span3'>"
						+ "<button type='button' id='contact-edit-row-update"+id+"' class='btn' onclick='updateContact("+id+")'>Save</button>"
					+ "</div>"
					+ "<div class='span2'>"
						+ "<button type='button' id='contact-edit-row-undo" +id+"' class='btn' onclick='undoContact("+id+")'>Undo</button>"
					+ "</div>"
					+ "<div class='span3'>"
						+ "<button type='button' id='contact-edit-row-delete" + id + "' class='btn' "
							+ (id == 0 ? "disabled" : "onclick='deleteContact("+id+")'")
							+ ">Delete</button>"
					+ "</div>"
					+ "<div class='span2'></div>"
				+ "</div>" 			
			+ "</div>"
		+ "</form>"; 		
		
		return html; 
	}

	function showEdit(id) {
		assert(id != 0); 	
		var contact = getContact(id); 
		
		$("#Contact" + id).after(contactEditHTML(contact)); 
		selectOption($("#EditContact" + id)
			.find("select[name='ContactType']"), contact.ContactType);
		$("#Contact" + id).hide(); 
		$("#EditContact" + id).show(); 
	}

	function myReset (id) {
		$("#EditContact" + id).find("[name='ContactDate']").popover('hide'); 
	}

	function undoContact (id) {
		document.getElementById("EditContact" + id).reset(); 
		myReset(id); 
		$("#EditContact" + id).hide(); 
		state.newContactShown = false; 
		
		if(id != 0) {
			$("#Contact" + id).show(); 
		}
	}

	function newContact() {
		if(!state.newContactShown) {
			$("#PutContactsHere").prepend(contactEditHTML()); 
			state.newContactShown = true; 
			$("#EditContact0").show(); 
		}
	}

	function updateContact(id) {
		if(checkDateInput(id)) {
			var editDiv = $("#EditContact" + id); 
		
			var data = {}; 
			data.REQ = "contact"; 
			data.ContactID = id; 
			data.UserEditID = <?php echo $_SESSION["id"] ?>; 
			data.ContactEditDate = currentSqlDate(); 
			data.ContactDate = editDiv.find("[name='ContactDate']").val(); 
			data.ContactTypeID = editDiv.find("[name='ContactType']").val(); 
			data.ContactSummary = editDiv.find("[name='ContactSummary']").val(); 
		
			displayContactType = editDiv.find("[name='ContactType']")
				.find("[value='" + data.ContactTypeID + "']").text(); 
			
			$.ajax({
				url : "ajax.php",
				type: "POST",
				data : data,
				success : function(r) {
					try {
						var response = $.parseJSON(r); 
					}
					catch (e) {
						throw "Error: server repsonse invalid."; 
					}
					
					if(response.Success) {
						$("#Contact" + id).attr('data-content', "<?php echo $_SESSION["username"] ?> on " + data.ContactEditDate); 
						$("#Contact" + id + "Type").html(displayContactType); 
						$("#Contact" + id + "Summary").html(data.ContactSummary); 
						undoContact(id); 
					}
					else {
						throw "Error: server response unsuccessful"; 
					}
				}, 
				error : function(e) {
					alert(e);  
				}
			}); 
			return; 
		}
	}
	
	function checkDateInput(id) {
		var dateInput = $("#EditContact" + id).find("[name='ContactDate']");		
		
		if(isValidDate(dateInput.val())) {
			var date_inputted = new Date(dateInput.val()); 
			dateInput.val(toSqlDate(date_inputted)); 
			return true; 
		}
		else {
			var msg = (isValidMysqlSyntax(dateInput.val()) ? 
					"the date you entered is invalid...please consult a calendar" : 
					"Format in the following way:  YYYY-MM-DD hh:mm:ss"); 					
			dateInput.popover({content : msg}); 
			dateInput.popover('show')
			dateInput.bind('click', function () {dateInput.popover('hide')}); 
			return false; 		
		}
	}	
	
	function display() {
		$("#PutContactsHere").html(""); 
		
		contacts.sort(function(a, b) {
			return (new Date(b.ContactDate)) - (new Date(a.ContactDate)); 
		}); 
		
		for(n in contacts) {
			var html = contactDisplayHTML(contacts[n]); 
			$("#PutContactsHere").append(html); 
		}
	}
</script>

<!-- MAIN SCRIPT FOR THIS PAGE HERE -->
<script>	
	$(document).ready(function() {
		/*for(n in contacts) {
			var html = contactDisplayHTML(contacts[n]); 
			$("#PutContactsHere").append(html); 
		}*/
		display(); 

		$(function() {
			$(".contact-form-row").popover({trigger: 'hover'}); 
		}); 
		
		$(function() {
			$(".contact-form-new").popover({trigger: 'hover'}); 
		}); 		
	}); 
</script>