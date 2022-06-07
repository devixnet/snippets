function flickity_scrolling_carousel( carousel_id ){
	let tickerSpeed = 1.5;
	let flickity = null;
	let isPaused = false;
	const flickity_carousel = document.getElementById( carousel_id );

	console.log( flickity_carousel );

	const flickity_update = () => {
		if (isPaused) return;

		if (flickity.slides) {
			flickity.x = (flickity.x - tickerSpeed) % flickity.slideableWidth;
			flickity.selectedIndex = flickity.dragEndRestingSelect();
			flickity.updateSelectedSlide();
			flickity.settle( flickity.x );
		}

		window.requestAnimationFrame( flickity_update );
	};

	const flickity_pause = () => {
		isPaused = true;
	};

	const flickity_play = () => {
		if (isPaused) {
			isPaused = false;
			window.requestAnimationFrame( flickity_update );
		}
	};

	flickity = new Flickity( flickity_carousel, {
		autoPlay: false,
		prevNextButtons: false,
		pageDots: false,
		draggable: true,
		wrapAround: true,
		selectedAttraction: 0.015,
		friction: 0.25
	});

	flickity.x = 0;

	flickity_carousel.addEventListener( 'mouseenter', flickity_pause, false );
	flickity_carousel.addEventListener( 'focusin', flickity_pause, false );
	flickity_carousel.addEventListener( 'mouseleave', flickity_play, false );
	flickity_carousel.addEventListener( 'focusout', flickity_play, false );

	flickity.on('dragStart', () => {
		isPaused = true;
	});

	flickity_update();
}