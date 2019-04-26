document.addEventListener("DOMContentLoaded", function(){

	var addRow = document.querySelector("#add");
	var elems = document.querySelectorAll(".add_education .cell_val");
	var bool;

	document.querySelector("#add").addEventListener("click", function(){
		addEducation(this)
	});

	/*Check - is additional rows for the edu filled*/
	for(let i=0; i < elems.length; i++){
		if(elems[i].textContent){
			bool = true;
		}
	}

	if(bool){
		addEducation(document.querySelector("#add"));
	}

	/*Show additional rows for the edu*/
	function addEducation(self){
		educations = document.querySelectorAll(".add_education");
		for(let i=0; i< educations.length; i++){
			educations[i].style.display = 'table-row';
		}
		self.style.display = 'none';
	}

})