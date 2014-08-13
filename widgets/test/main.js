var C = crel2;

var ventana = API.widget.create();

var textarea;

C(ventana, 
	C('button', ['onclick', set_test], 'SET test'),
	C('button', ['onclick', get_test], 'GET test'),
	C('button', ['onclick', delete_test], 'DELETE test'),
	C('button', ['onclick', delete_all_test], 'DELETE ALL test'),
	C('button', ['onclick', exists], 'EXISTS test'),
	C('button', ['onclick', global_set_test], 'GLOBAL SET test'),
	C('button', ['onclick', global_get_test], 'GLOBAL GET test'),
	C('button', ['onclick', global_delete_test], 'GLOBAL DELETE test'),
	C('button', ['onclick', global_exists], 'GLOBAL EXISTS test'),
	C('br'),
	textarea = C('textarea')
);

function log(what){
	textarea.value += what+"\n";
}



//log(API.url(widgetID,'main.js'));
// Variables are saved as text


/////////////////////////////
// SET test
/////////////////////////////
function set_test(){
	var text_rnd = Math.random();
	log('SET Test Saving the text: '+text_rnd);

	API.storage.remoteStorage.set('test', text_rnd, function(entrada){
		if(entrada){
			log('SET Test Text Saved.');
		}
		else{
			log('SET Test Text NOT saved.');
		}
	});
}



/////////////////////////////
// GET test
/////////////////////////////
function get_test(){
	log('GET Test');
	
	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('GET Test Got the text: '+entrada);
		}
		else{
			log('GET Test There is not a saved variable with that name.');
		}
	});
}



/////////////////////////////
// DELETE test
/////////////////////////////
function delete_test(){
	log('DELETE Test');

	API.storage.remoteStorage.delete('test', function(entrada){
		if(entrada){
			log('DELETE Test deleted OK.');
		}
		else{
			log('DELETE Test deleted FAIL.');
		}
	});

	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('DELETE Test confirmed FAIL.');
		}
		else{
			log('DELETE Test confirmed OK.');
		}
	});
}



/////////////////////////////
// DELETE ALL test
/////////////////////////////
function delete_all_test(){
	log('DELETE ALL Test');

	API.storage.remoteStorage.deleteAll(function(entrada){
		if(entrada){
			log('DELETE ALL Test deleted OK.');
		}
		else{
			log('DELETE ALL Test deleted FAIL.');
		}
	});

	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('DELETE ALL Test confirmed FAIL.');
		}
		else{
			log('DELETE ALL Test confirmed OK.');
		}
	});
}



/////////////////////////////
// EXISTS test
/////////////////////////////
function exists(){
	log('EXISTS Test');

	API.storage.remoteStorage.exists('test', function(entrada){
		if(entrada){
			log('EXISTS Test variable exists = YES.');
		}
		else{
			log('EXISTS Test variable exists = NO.');
		}
	});
}



/////////////////////////////
// GLOBAL SET test
/////////////////////////////
function global_set_test(){
	var text_rnd = Math.random();
	log('GLOBAL SET Test Saving the text: '+text_rnd);

	API.storage.sharedStorage.set('test', text_rnd, function(entrada){
		if(entrada){
			log('GLOBAL SET Test Text Saved.');
		}
		else{
			log('GLOBAL SET Test Text NOT saved.');
		}
	});
}



/////////////////////////////
// GLOBAL GET test
/////////////////////////////
function global_get_test(){
	log('GLOBAL GET Test');
	
	API.storage.sharedStorage.get('test', function(entrada){
		if(entrada){
			log('GLOBAL GET Test Got the text: '+entrada);
		}
		else{
			log('GLOBAL GET Test There is not a saved variable with that name.');
		}
	});
}



/////////////////////////////
// GLOBAL DELETE test
/////////////////////////////
function global_delete_test(){
	log('GLOBAL DELETE Test');

	API.storage.sharedStorage.delete('test', function(entrada){
		if(entrada){
			log('GLOBAL DELETE Test deleted OK.');
		}
		else{
			log('GLOBAL DELETE Test deleted FAIL.');
		}
	});

	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('GLOBAL DELETE Test confirmed FAIL.');
		}
		else{
			log('GLOBAL DELETE Test confirmed OK.');
		}
	});
}



/////////////////////////////
// GLOBAL EXISTS test
/////////////////////////////
function global_exists(){
	log('GLOBAL EXISTS Test');

	API.storage.sharedStorage.exists('test', function(entrada){
		if(entrada){
			log('GLOBAL EXISTS Test variable exists = YES.');
		}
		else{
			log('GLOBAL EXISTS Test variable exists = NO.');
		}
	});
}