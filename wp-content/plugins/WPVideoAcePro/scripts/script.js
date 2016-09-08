var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var $ = jQuery;
var yt_players = {};
var yt_fplayer = null;

function onYouTubeIframeAPIReady() {
	$(".cytpro-outer-container").each( function( idx, val ) {
		var self = $(val);

		var player = null;
		if ( self.data('vtype') == 'type_youtube' ) {
			var pv = { 
					'autoplay'	: (idx == 0 && self.data('autoplay')=='y')?1:0, 
					'controls'	: (self.data('showcontrol')=='y')?1:0,
					'loop'		: (self.data('enableloop')=='y')?1:0, // currently not working
					'showinfo'	: (self.data('showinfo')=='y')?1:0,
					'fs'		: (self.data('enablefs')=='y')?1:0,
					'iv_load_policy'	: (self.data('enablean')=='y')?1:3,
					'modestbranding'	: (self.data('showlogo')=='y')?0:1
			};
			
			if ( self.data('enableloop')=='y') {
				pv.playlist	= self.data('vid');
			}
			
			player = new YT.Player('cytpro-' + self.data('cid'), {
				height: '390',
				width: '640',
				videoId: self.data('vid'),
				playerVars: pv,
				events: {
					'onReady'					: onPlayerReady,
					'onStateChange'				: onPlayerStateChange,
					'onPlaybackQualityChange'	: onPlayerPlaybackQualityChange,
				}
			});

			yt_players[self.data('cid')] = {
				ytobj: player,
				jqobj: self,
				type: self.data('vtype'),
				oldVolume: -1
			};
		} else {
			$('video#cytpro-ml-' + self.data('cid')).mediaelementplayer( {
				loop: (self.data('enableloop')=='y') ? true : false,
				alwaysShowControls: (self.data('showcontrol')=='y')? true : false,

				success: function (mediaElement, domObject) { 
					// 	add event listener
					var oldVolume = -1;

					mediaElement.addEventListener('timeupdate', function(e) {
						var time = mediaElement.currentTime;

						if ( self.data( 'start' ) <= time && self.data( 'end' ) >= time ) {
							if ( self.data('vfeature') == 'full' ) {
								self.addClass('full');
							} else if ( self.data('vfeature') == 'resize' ) {
								self.addClass('resize');
								var parentContainer = self.find('.cytpro-thumb-container');

								if ( parentContainer.hasClass('cytpro-pos-top') || parentContainer.hasClass('cytpro-pos-bottom') ) {
									self.find('.cytpro-frame').css('height', self.data('resize') + '%' ).css('width', self.data('resize') + '%' );
									self.find('.cytpro-content').css('height', ( 100 - self.data('resize') ) + '%').show();
								}
								else {
									self.find('.cytpro-frame').css('width', self.data('resize') + '%' ).css('height', self.data('resize') + '%' );
									self.find('.cytpro-content').css('width', ( 100 - self.data('resize') ) + '%').show();
								}
							} else if ( self.data('vfeature') == 'locked' ) {
								$(".cytpro-locked-content[data-pid='" + self.data('pid') + "']").show();
							}

							oldVolume = mediaElement.volume;
							mediaElement.setVolume( parseInt( self.data('volume') ) / 10 );
						}
						else {
							if ( self.data('vfeature') == 'full' ) {
								self.removeClass('full');
							}
							else if ( self.data('vfeature') == 'resize' ) {
								self.removeClass('resize');
								self.find('.cytpro-frame').css('width', '').css('height', '');
								self.find('.cytpro-content').css('width', '').css('height', '').hide();
							}
							else if ( self.data('vfeature') == 'locked' ) {
								$(".cytpro-locked-content[data-pid='" + self.data('pid') + "']").hide();
							}

							if ( oldVolume > 0 ) mediaElement.setVolume( oldVolume );
							oldVolume = -1;
						}
					}, false);

					mediaElement.addEventListener('playing', function(e) {
						// pause all other players play
						for( var p in yt_players ) {
							if ( yt_players[p].ytobj == mediaElement ) {
								continue;
							}

							if ( yt_players[p].type == 'type_youtube' ) {
								yt_players[p].ytobj.pauseVideo();
							}
							else {
								yt_players[p].ytobj.pause();
							}
						}
					});

					if ( idx == 0 && self.data('autoplay') == 'y' ) {
						mediaElement.play();
					}

					yt_players[self.data('cid')] = {
						ytobj: mediaElement,
						jqobj: self,
						type: self.data('vtype')
					};
				}
			} );
		}

		if( yt_fplayer === null ) {
			yt_fplayer = {
				jqobj: self,
				id: self.data('vid'),
				type: self.data('vtype')
			};

			if ( self.data('thumb') === 'y' ) { // show thumb on scroll will work only for the first player
				initScroll();
			}
		}
	});

	keepChecking();
}

function onPlayerReady(event) {
}

function onPlayerStateChange(event) {
	var iframe = event.target.getIframe();
	var container = $(iframe).closest('.cytpro-outer-container');

	if (event.data == YT.PlayerState.PLAYING) {
		// console.log( 'changing video quality...' + container.data('quality') );
		event.target.setPlaybackQuality( container.data('quality') );

		// pause all other players play
		for( var p in yt_players ) {
			if ( yt_players[p].ytobj == event.target ) {
				continue;
			}

			if ( yt_players[p].type == 'type_youtube' ) {
				yt_players[p].ytobj.pauseVideo();
			}
			else {
				yt_players[p].ytobj.pause();
			}
		}
	}
}

function onPlayerPlaybackQualityChange() {
	// console.log( 'video quality changed...');
}

function stopVideo() {
	for( var p in yt_players ) {
		yt_players[p].jqobj.removeClass('full');
	}
}

function keepChecking() { // youtube time tracking
	setTimeout( function() {
		for( var p in yt_players ) {
			try {
				if ( yt_players[p].type == 'type_youtube' ) { 
					var time = yt_players[p].ytobj.getCurrentTime();
					if ( yt_players[p].ytobj.getPlayerState() != 1 ) continue; 

					if ( yt_players[p].jqobj.data( 'start' ) <= time && yt_players[p].jqobj.data( 'end' ) >= time ) {

						if ( yt_players[p].modified == 1 ) { continue; }

						if ( yt_players[p].jqobj.data('vfeature') == 'full' ) {
							yt_players[p].jqobj.addClass('full');
						} else if ( yt_players[p].jqobj.data('vfeature') == 'resize' ) {
							yt_players[p].jqobj.addClass('resize');
							var parentContainer = yt_players[p].jqobj.find('.cytpro-thumb-container');

							if ( parentContainer.hasClass('cytpro-pos-top') || parentContainer.hasClass('cytpro-pos-bottom') ) {
								yt_players[p].jqobj.find('.cytpro-frame').css('height', yt_players[p].jqobj.data('resize') + '%' ).css('width', yt_players[p].jqobj.data('resize') + '%' );
								yt_players[p].jqobj.find('.cytpro-content').css('height', ( 100 - yt_players[p].jqobj.data('resize') ) + '%').show();
							}
							else {
								yt_players[p].jqobj.find('.cytpro-frame').css('width', yt_players[p].jqobj.data('resize') + '%' ).css('height', yt_players[p].jqobj.data('resize') + '%' );
								yt_players[p].jqobj.find('.cytpro-content').css('width', ( 100 - yt_players[p].jqobj.data('resize') ) + '%').show();
							}
						} else if ( yt_players[p].jqobj.data('vfeature') == 'locked' ) {
							$(".cytpro-locked-content[data-pid='" + yt_players[p].jqobj.data('pid') + "']").show();
						}

						yt_players[p].modified = 1;
						yt_players[p].oldVolume = yt_players[p].ytobj.getVolume();
						yt_players[p].ytobj.setVolume( yt_players[p].jqobj.data('volume') * 10 );
					}
					else {
						if ( yt_players[p].modified == 0 ) { continue; }

						if ( yt_players[p].jqobj.data('vfeature') == 'full' ) {
							yt_players[p].jqobj.removeClass('full');
						} else if ( yt_players[p].jqobj.data('vfeature') == 'resize' ) {
							yt_players[p].jqobj.removeClass('resize');
							yt_players[p].jqobj.find('.cytpro-frame').css('width', '').css('height', '');
							yt_players[p].jqobj.find('.cytpro-content').css('width', '').css('height', '').hide();
						} else if ( yt_players[p].jqobj.data('vfeature') == 'locked' ) {
							$(".cytpro-locked-content[data-pid='" + yt_players[p].jqobj.data('pid') + "']").hide();
						}

						yt_players[p].modified = 0;
						if ( yt_players[p].oldVolume > -1 ) yt_players[p].ytobj.setVolume( yt_players[p].oldVolume );
					}
				}
			}
			catch(err) {
				// console.log( err );
			}
		}

		keepChecking();
	}, 500 );
}

function initScroll() {
	var prevScrollPos = 0;

	$(document).ready( function() {
		$(window).scrollTop( prevScrollPos );

		$(window).on( 'scroll', function() {
			var currScrollPos = $(window).scrollTop();

			if ( ! yt_fplayer.jqobj.hasClass('full') ) {
				if ( yt_fplayer.jqobj.offset().top < currScrollPos ) {
					if ( currScrollPos > prevScrollPos ) { // direction: down;
						yt_fplayer.jqobj.find('.cytpro-thumb-container').addClass('thumb');
						$(window).trigger('resize');
					}
				}
				else {
					if ( currScrollPos <= prevScrollPos ) { // direction: down;
						yt_fplayer.jqobj.find('.cytpro-thumb-container').removeClass('thumb');
						$(window).trigger('resize');
					}
				}
			}

			prevScrollPos = $(window).scrollTop();
		});
	});
}