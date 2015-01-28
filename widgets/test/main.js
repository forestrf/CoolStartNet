var C = crel2;

var ventana = API.widget.create();

var textarea;

C(ventana, 
	C('button', ['onclick', local_set_test], 'LOCAL SET test'),
	C('button', ['onclick', local_get_test], 'LOCAL GET test'),
	C('button', ['onclick', local_delete_test], 'LOCAL DELETE test'),
	C('button', ['onclick', local_delete_all_test], 'LOCAL DELETE ALL test'),
	C('button', ['onclick', local_exists], 'LOCAL EXISTS test'),
	C('br'),
	C('button', ['onclick', remote_set_test], 'REMOTE SET test'),
	C('button', ['onclick', remote_get_test], 'REMOTE GET test'),
	C('button', ['onclick', remote_delete_test], 'REMOTE DELETE test'),
	C('button', ['onclick', remote_delete_all_test], 'REMOTE DELETE ALL test'),
	C('button', ['onclick', remote_exists], 'REMOTE EXISTS test'),
	C('br'),
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
// LOCAL
/////////////////////////////
function local_set_test(){
	var text_rnd = Math.random();
	log('LOCAL SET Test Saving the text: '+text_rnd);

	API.storage.localStorage.set('test', text_rnd, function(entrada){
		if(entrada){
			log('LOCAL SET Test Text Saved.');
		}
		else{
			log('LOCAL SET Test Text NOT saved.');
		}
	});
}

function local_get_test(){
	log('LOCAL GET Test');
	
	API.storage.localStorage.get('test', function(entrada){
		if(entrada){
			log('LOCAL GET Test Got the text: '+entrada);
		}
		else{
			log('LOCAL GET Test There is not a saved variable with that name.');
		}
	});
}

function local_delete_test(){
	log('LOCAL DELETE Test');

	API.storage.localStorage.delete('test', function(entrada){
		if(entrada){
			log('LOCAL DELETE Test deleted OK.');
		}
		else{
			log('LOCAL DELETE Test deleted FAIL.');
		}
	});

	API.storage.localStorage.get('test', function(entrada){
		if(entrada){
			log('LOCAL DELETE Test confirmed FAIL.');
		}
		else{
			log('LOCAL DELETE Test confirmed OK.');
		}
	});
}

function local_delete_all_test(){
	log('LOCAL DELETE ALL Test');

	API.storage.localStorage.deleteAll(function(entrada){
		if(entrada){
			log('LOCAL DELETE ALL Test deleted OK.');
		}
		else{
			log('LOCAL DELETE ALL Test deleted FAIL.');
		}
	});

	API.storage.localStorage.get('test', function(entrada){
		if(entrada){
			log('LOCAL DELETE ALL Test confirmed FAIL.');
		}
		else{
			log('LOCAL DELETE ALL Test confirmed OK.');
		}
	});
}

function local_exists(){
	log('LOCAL EXISTS Test');

	API.storage.localStorage.exists('test', function(entrada){
		if(entrada){
			log('LOCAL EXISTS Test variable exists = YES.');
		}
		else{
			log('LOCAL EXISTS Test variable exists = NO.');
		}
	});
}



/////////////////////////////
// REMOTE
/////////////////////////////
function remote_set_test(){
	var text_rnd = Math.random();
	log('REMOTE SET Test Saving the text: '+text_rnd);

	API.storage.remoteStorage.set('test', text_rnd, function(entrada){
		if(entrada){
			log('REMOTE SET Test Text Saved.');
		}
		else{
			log('REMOTE SET Test Text NOT saved.');
		}
	});
}

function remote_get_test(){
	log('REMOTE GET Test');
	
	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('REMOTE GET Test Got the text: '+entrada);
		}
		else{
			log('REMOTE GET Test There is not a saved variable with that name.');
		}
	});
}

function remote_delete_test(){
	log('REMOTE DELETE Test');

	API.storage.remoteStorage.delete('test', function(entrada){
		if(entrada){
			log('REMOTE DELETE Test deleted OK.');
		}
		else{
			log('REMOTE DELETE Test deleted FAIL.');
		}
	});

	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('REMOTE DELETE Test confirmed FAIL.');
		}
		else{
			log('REMOTE DELETE Test confirmed OK.');
		}
	});
}

function remote_delete_all_test(){
	log('REMOTE DELETE ALL Test');

	API.storage.remoteStorage.deleteAll(function(entrada){
		if(entrada){
			log('REMOTE DELETE ALL Test deleted OK.');
		}
		else{
			log('REMOTE DELETE ALL Test deleted FAIL.');
		}
	});

	API.storage.remoteStorage.get('test', function(entrada){
		if(entrada){
			log('REMOTE DELETE ALL Test confirmed FAIL.');
		}
		else{
			log('REMOTE DELETE ALL Test confirmed OK.');
		}
	});
}

function remote_exists(){
	log('REMOTE EXISTS Test');

	API.storage.remoteStorage.exists('test', function(entrada){
		if(entrada){
			log('REMOTE EXISTS Test variable exists = YES.');
		}
		else{
			log('REMOTE EXISTS Test variable exists = NO.');
		}
	});
}



/////////////////////////////
// GLOBAL
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

	API.storage.sharedStorage.get('test', function(entrada){
		if(entrada){
			log('GLOBAL DELETE Test confirmed FAIL.');
		}
		else{
			log('GLOBAL DELETE Test confirmed OK.');
		}
	});
}

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
