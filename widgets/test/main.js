//console.log(API.url(widgetID,'main.js'));
// Variables are saved as text

console.log('Test 1');

/////////////////////////////
// SET test
/////////////////////////////

var text_rnd = Math.random();
console.log('Test 1 Saving the text: '+text_rnd);

API.Storage.remoteStorage.set('test', text_rnd, function(entrada){
	if(entrada){
		console.log('Test 1 Text Saved.');
	}
	else{
		console.log('Test 1 Text NOT saved.');
	}
});



/////////////////////////////
// GET test
/////////////////////////////

API.Storage.remoteStorage.get('test', function(entrada){
	if(entrada){
		console.log('Test 1 Got the text: '+entrada);
	}
	else{
		console.log('Test 1 There is not a saved variable with that name.');
	}
});

// ----------------------------------------------------------------------------------------------------------------



console.log('Test 2');

/////////////////////////////
// Global variable SET test
/////////////////////////////

var text_rnd = Math.random();
console.log('Test 2 Saving the text: '+text_rnd);

API.Storage.sharedStorage.set('test', text_rnd, function(entrada){
	if(entrada){
		console.log('Test 2 Text Saved.');
	}
	else{
		console.log('Test 2 Text NOT saved.');
	}
});



/////////////////////////////
// Global variable GET test
/////////////////////////////

API.Storage.sharedStorage.get('test', function(entrada){
	if(entrada){
		console.log('Test 2 Got the text: '+entrada);
	}
	else{
		console.log('Test 2 There is not a saved variable with that name.');
	}
});
