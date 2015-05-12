
// AJAX-call to dugga.js 
AJAXService("TOOLBAR", {}, "UMVTEACHER");

var selectedClass = "";
var selectedClassCode = "";

//---------------------------------------------------------------
//	renderTeacherView(data) - renders given html code for the given
//	data that was returned.
//---------------------------------------------------------------
function renderTeacherView(data) 
{
	var type = data['type']; //Array that holds what type of data should be rendered from DB

	// render the created toolbar
	if(type == "TOOLBAR") {
		createToolbar(data['classes']);
	}
	//Render the studentlist 
	else if(type == "VIEW") {
		renderView(data);
	}

	// Check if error occurred during execution of SQL queries
	if(data['debug'] != "NONE!") {
		alert(data['debug']);
	}
	navigate_page();
}

//---------------------------------------------------------------
//	createToolbar(classes) - creates the the toolbar with the
//	given classes that the teacher is responsible for.
//---------------------------------------------------------------
function createToolbar(classes) 
{
	//Check if there is a class that can be placed in the toolbar
	if(classes.length > 0) {
		renderToolbar(classes);
		updateDefaultProgram(classes); // Updates the view for the default class
	}
	
}

//---------------------------------------------------------------
//	renderToolbar(classes) - renders the toolbar with the given
//	classnames, sorts under the given coursecode
//---------------------------------------------------------------
function renderToolbar(classes) 
{
	var htmlStr = "";
	var currentClassCode = "";
	// Close current open lists
	htmlStr += "<ul>";
	
	//Loop to list every class into a dropdownmenu for the teacher
	for(var i = 0; i < classes.length; i++) {
		current_class = classes[i];
		if(currentClassCode != current_class['classcode']) {	//Check if course has a new course code
			currentClassCode = current_class['classcode'];
			if(i > 0) {
				htmlStr += "</ul>";
			}
			// Close current open lists
			htmlStr += "</li>";
			htmlStr += "<li id='"+currentClassCode+"' class='noActive'><a>" + currentClassCode + "</a>";
			htmlStr += "<ul>";
		}
		htmlStr += "<li class='noActive' id='"+current_class['class']+"' onclick='updateView(" + "\"" + 		current_class['class'] + "\", \"" + current_class['classcode'] + "\")'><a>" + current_class['class'] + "</a></li>";
    }
    
    htmlStr += "</ul>" + "</li>" + "</ul>";
    
    //Place everything that is retrived as a class and put it into a dropdownmenu.
    var menulist = document.getElementById('DropdownMenu');
    menulist.innerHTML = htmlStr;
}

//---------------------------------------------------------------
// selects the active menu and sets the css 
//---------------------------------------------------------------
function markAsActive(classname_id, classcode) 
{
	if(selectedClass !== classname_id) {
		if(selectedClass !== "") {
			$("#" + selectedClass).removeClass('active');
			$("#" + selectedClass).addClass('noActive');
			//Remove active from classcode 
			$("#" + selectedClassCode).removeClass('active');
			$("#" + selectedClassCode).addClass('noActive');
		}
		$("#" + classname_id).removeClass('noActive');
		$("#" + classname_id).addClass('active');
		
		$("#" + classcode).removeClass('noActive');
		$("#" + classcode).addClass('active');
		
		// Save current selected for later use
		selectedClass = classname_id;
		selectedClassCode = classcode;
	}
}

//---------------------------------------------------------------
//	getTheDefaultProgram(classes) - returns the first class in the
//	the array that represents the default choice
//---------------------------------------------------------------
function updateDefaultProgram(classes) 
{
	updateView(classes[0]['class'], classes[0]['classcode']);
}

//---------------------------------------------------------------
//	updateView(classname) - calls the ajaxservice that renders
//	the view for the given class
//---------------------------------------------------------------
function updateView(classname, classcode) 
{
	markAsActive(classname, classcode);
	// AJAX call to request students of 'classname' 
	AJAXService("VIEW", {classname:classname}, "UMVTEACHER");
}

//---------------------------------------------------------------
//	renderView(data) - renders the view from the
//	teacher. Will render the full view with title and everything.
//---------------------------------------------------------------
function renderView(data)
{
	clearLinearGraph();
	
	var htmlStr = "";
	var classname = data['classname'];
	var studentlist = data['studentlist'];
	var calcNumberOfStudents = null;
	//change this varible for the number of students that renders per page
	var numberOfStudentsPerPages = 8; 
	
	//Render title
	htmlStr += "<h2>" + classname + "</h2>";
	
	//program title
	var programTitle = document.getElementById("title");
	programTitle.innerHTML = htmlStr;
	
	htmlStr = "";
	var wichPage=1;
	var renderStudent = 0;

	
	//If there is no students this will execute.
	if(studentlist.length == 0) {
		htmlStr = "<div id='no_page'><h2>No student data found for this class.</h2></div>";
		$('.changePages').hide();
		$('#radio_buttonToolbar').hide();

	}else{

		for(var i = 0; i <= studentlist.length; i+=numberOfStudentsPerPages){
		

		if(wichPage>1){
			htmlStr += "<div id='page_"+wichPage+"' class='student_pages' style='display:none;'>";
		}else{

			htmlStr += "<div id='page_"+wichPage+"' class='student_pages'>";
		}

		for(var j = 0; j < numberOfStudentsPerPages;j++) {
		
			var student = studentlist[renderStudent];

			htmlStr += "<div class='studentInfo'>";
			htmlStr += getStudentInfo(student, renderStudent);
			htmlStr += getCourseResults(student['results']);

			htmlStr += "</div>";
		
			calcNumberOfStudents++;
			//error handling so it breaks when all the students are render
			if(renderStudent+1==studentlist.length){
				break;
			}
			renderStudent++;	
		}
		wichPage++;
		htmlStr += "</div>";
		}
	
		render_next_pages(calcNumberOfStudents,numberOfStudentsPerPages);


		$('#radio_buttonToolbar').show();
		onClick_Students_To_page();
	}
	
	var studentView = document.getElementById("studentslist");
	studentView.innerHTML = htmlStr;
	
	//The line graph needs to be redrawn once for the text to appear correctly
	createLinearGraph(data);
	clearLinearGraph();
	createLinearGraph(data);
	onClick_Students_To_page();
}

//---------------------------------------------------------------
//	getStudentInfo(student, number) - Creates a div that shows 
//	a list of every student of a specific class. Fullname, ssn
//	username and email.
//---------------------------------------------------------------
function getStudentInfo(student, number) 
{
	var htmlStr = "";
	if(number % 2 == 0) {
		htmlStr += "<div id='"+student['uid']+"' class='student'>";
	}else {
		htmlStr += "<div id='"+student['uid']+"' class='student odd'>";
	}
	htmlStr += "<div class='student_name'><p>" + student['fullname'] + "</p></div>";
	htmlStr += "<div class='student_ssn'><p>" + student['ssn'] + "</p></div>";
	htmlStr += "<div class='student_username'><p>" + student['username'] + "</p></div>";
	htmlStr += "<div class='student_email'><p>" + student['email'] + "</p></div>";
		
	htmlStr += "</div>";
	
	return htmlStr;
}

//---------------------------------------------------------------
//	getCourseResults(results) - creates the html representation
//	of the course results for a student and returns it
//---------------------------------------------------------------

function getCourseResults(results)
{
	var colorGreen = "#50a750";
	var colorYellow = "#F0AD4E";
	var htmlStr = "";
		
	htmlStr += "<div class='students_results'>";
	
	for(var i = 0; i < results.length; i++) {
	
		/* Check that result is not null and set to 0 if so */
		var course_result = results[i]['result'] == null ? 0 : results[i]['result'];
		var course_hp	  = results[i]['hp'];
		var procent		  = course_result/course_hp * 100;
		var color		  = procent < 100 ? colorYellow : colorGreen;
		
		htmlStr += "<div class='progress_course_total'>";
		htmlStr += "<div class='progress_course' style='width:" + procent + "%; background-color: " + color + ";'>";
		htmlStr += "<p>" + parseFloat(course_result) + "/" + course_hp + "</p>";
		htmlStr += "</div>";
		htmlStr += "</div>";
		
	}
		
	htmlStr += "</div>";
	
	return htmlStr;
	
}

/* Sets the number of change pages buttons depending of how many students in class*/
function render_next_pages(calcNumberOfStudents,numberOfStudentsPerPages){
	var htmlInserts="";
	var numberOfPage=1;

	htmlInserts+="<div class='changePages'>";
	htmlInserts+="<p>Page</p>";

	for(var i =0; i < calcNumberOfStudents; i+=numberOfStudentsPerPages){
		htmlInserts+= "<div class='page_"+numberOfPage +" pages'>"+numberOfPage +"</div>";
		numberOfPage++;
	}

	htmlInserts+= "<div id='nextPage'> >> </div> ";	
	htmlInserts+="</div>";

	var changePages = document.getElementById("teacher_pages");
	changePages.innerHTML = htmlInserts;

}

//navigates between pages.
function navigate_page(){
	$('.pages').click(function(){
		
		var classPage = "#"+this.className.split(' ')[0]; 
		
		// hides all studen_pages before the right one is displayesd
		$('.student_pages').each(function(){
			$(this).css("display", "none");
		});
		$(classPage).css("display", "block");
		
	});
}

//---------------------------------------------------------------
//	clearLinearGraph() - clears the line graph representing
//	all the student results in every course
//---------------------------------------------------------------

function clearLinearGraph() 
{
	var graph = $('#graph');
	var c = graph[0].getContext('2d');
	c.clearRect(0, 0, graph[0].width, graph[0].height);
}

// Redirect teacher to specific student page
function onClick_Students_To_page(){
	$('.student').click(function(){
		get_student_data(this.id);
	});
}

function get_student_data(studentid) {
	var renderstudent = 'render';
	$.ajax({
		type:"POST",
		url: "../UserManagementView/usermanagementviewservice.php",
		data: {
			studentid: studentid,
			renderstudent: renderstudent
		},
		success:function(data) {
			var result = JSON.parse(data);
			renderStudentView(result);
		},
		error:function() {
			console.log("error");
		}
	});
}

//---------------------------------------------------------------
//	createLinearGraph() - creates the line graph
//	representing all the student results in every course
//---------------------------------------------------------------

function createLinearGraph(data)
{
	var backgroundcolor_overlay = "#F5F0F5";
	var color_helpLines 		= "#D8D8D8";
	var width_helpLines			= 2;
	var x_axis_text_font			= 'italic 8pt sans-serif';
	var x_axis_text_align		= "center";
	var x_axis_text_color		= "#000";
	var width_axisLines			= 2;
	var color_axisLines			= "#333";
	var y_axis_text_baseline	= "middle";
	var y_axis_text_align		= "right";
	var graphLine_color			= "#5C005C";
	var graphLine_width			= 4;
	var circle_fill_color		= '#FFF';
	var circle_stroke_color		= '#D8D8D8';
	var circle_width			= 10;
	
	
	var graph;
    var yPadding_top = 20;
    var yPadding_bottom = 30;
	var xPadding = 40;
	var maxY = 100;
	
	var data = { values:[
		{ X: "Kurs", Y: 50 },
		{ X: "Kurs", Y: 67 },
		{ X: "Kurs", Y: 86 },
		{ X: "Kurs", Y: 76 },
		{ X: "Kurs", Y: 55 },
		{ X: "Kurs", Y: 89 },
		{ X: "Kurs", Y: 34 },
		{ X: "Kurs", Y: 34 },
		{ X: "Kurs", Y: 67 },
		{ X: "Kurs", Y: 22 },
		{ X: "Kurs", Y: 64 },
		{ X: "Kurs", Y: 74 },
		{ X: "Kurs", Y: 87 },
		{ X: "Kurs", Y: 16 },
	]};
	
	// Return the x pixel for a graph point
	function getXPixel(val) {
		return ((graph.width() - xPadding) / data.values.length) * val + (xPadding * 2);
	}
	
	// Return the y pixel for a graph point
	function getYPixel(val) {
		return graph.height() - (((graph.height() - (yPadding_top + yPadding_bottom)) / maxY) * val) - yPadding_bottom;
	}
	 
	graph = $('#graph');

	var c = graph[0].getContext('2d');

	/* style for the overlay under the graph */
	c.fillStyle = backgroundcolor_overlay;
	
	/* begin the drawing of the polygon that should be under the graph */
	c.beginPath();
	c.moveTo(getXPixel(0), getYPixel(0));
	
	for(var i = 0; i < data.values.length; i++) {
		c.lineTo(getXPixel(i), getYPixel(data.values[i].Y));
	}
	
	c.lineTo(getXPixel(data.values.length - 1), getYPixel(0));
	c.fill();
	
	c.lineWidth 	= width_helpLines;
	c.strokeStyle 	= color_helpLines;
	c.font 			= x_axis_text_font;
	c.textAlign 	= x_axis_text_align;
	c.fillStyle 	= x_axis_text_color;
	
	// Draw the X value texts
	for(var i = 0; i < data.values.length; i ++) {
		c.beginPath();
		c.fillText(data.values[i].X, getXPixel(i), graph.height() - yPadding_bottom + 20);
		c.moveTo(getXPixel(i), graph.height() - yPadding_bottom);
		c.lineTo(getXPixel(i), getYPixel(100));
		c.stroke();
	}
	
	c.lineWidth = width_axisLines;
	c.strokeStyle = color_axisLines;
	
	// Draw the axises
	c.beginPath();
	c.moveTo(xPadding, yPadding_top);
	c.lineTo(xPadding, graph.height() - yPadding_bottom);
	c.lineTo(graph.width(), graph.height() - yPadding_bottom);
	c.stroke();
	
	// Draw the Y value texts
	c.textAlign = y_axis_text_align;
	c.textBaseline = y_axis_text_baseline;
	
	c.fillText((0 + "%"), xPadding - 5, getYPixel(0));
	c.fillText((100 + "%"), xPadding - 5, getYPixel(100));
	
	c.strokeStyle = graphLine_color;
	c.lineWidth = graphLine_width;
	
	// Draw the line graph
	c.beginPath();
	c.moveTo(getXPixel(0), getYPixel(data.values[0].Y));
	for(var i = 1; i < data.values.length; i ++) {
		c.lineTo(getXPixel(i), getYPixel(data.values[i].Y));
	}
	c.stroke();
	
	// Draw the dots
	c.fillStyle = circle_fill_color;
	c.strokeStyle = circle_stroke_color;
					
	for(var i = 0; i < data.values.length; i ++) {  
		c.beginPath();
		c.arc(getXPixel(i), getYPixel(data.values[i].Y), circle_width, 0, Math.PI * 2, true);
		c.fill();
		c.stroke();
	}
}