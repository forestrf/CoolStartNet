var C = crel2;

var ventana = API.widget.create();

var textarea;

C(ventana, 
	C('button', ['onclick', set_test], 'SET test'),
	C('button', ['onclick', get_test], 'GET test'),
	C('button', ['onclick', delete_test], 'DELETE test'),
	C('button', ['onclick', global_set_test], 'GLOBAL SET test'),
	C('button', ['onclick', global_get_test], 'GLOBAL GET test'),
	C('button', ['onclick', global_delete_test], 'GLOBAL DELETE test'),
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