//alert(API.url(widgetID,'main.js'));

alert('Test 1');

/////////////////////////////
// SET test
/////////////////////////////

var text_rnd = Math.random();
alert('Saving the text: '+text_rnd);

var comando = {
	'action':'set',
	'widget':widgetID,
	'variables':{'test':text_rnd}
};

API.call(comando, function(entrada){
	if(entrada['test']){
		alert('Text Saved.');
	}
	else{
		alert('Text NOT saved.');
	}
});



/////////////////////////////
// GET test
/////////////////////////////

var comando = {
	'action':'get',
	'widget':widgetID,
	'variables':'test'
};

API.call(comando, function(entrada){
	if(entrada['test']){
		alert('Got the text: '+entrada['test']);
	}
	else{
		alert('There is not a saved variable with that name.');
	}
});

// ----------------------------------------------------------------------------------------------------------------



alert('Test 2');

/////////////////////////////
// Multiple SET test
/////////////////////////////

var text_rnd1 = Math.random();
var text_rnd2 = Math.random();
var text_rnd3 = Math.random();
alert('Saving the texts:\n'+text_rnd1+'\n'+text_rnd2+'\n'+text_rnd3);

var comando = {
	'action':'set',
	'widget':widgetID,
	'variables':{'test1':text_rnd1,'test2':text_rnd2,'test3':text_rnd3}
};

API.call(comando, function(entrada){
	for(var i in entrada){
		if(entrada[i]){
			alert('Text Saved ('+i+').');
		}
		else{
			alert('Text NOT saved ('+i+').');
		}
	}
});



/////////////////////////////
// Multiple GET test
/////////////////////////////

var comando = {
	'action':'get',
	'widget':widgetID,
	'variables':['test1','test2','test3']
};

API.call(comando, function(entrada){
	for(var i in entrada){
		if(entrada[i]){
			alert('Got the text ('+i+'): '+entrada[i]);
		}
		else{
			alert('There is not a saved variable with that name ('+i+').');
		}
	}
});