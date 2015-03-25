window.addEvent('domready', function(){		
	var mySlide = new Fx.Slide('sbsmile');
	mySlide.hide();	
	$('toggle').addEvent('click', function(e){
		e = new Event(e);
		mySlide.toggle();
		e.stop();
	});
});