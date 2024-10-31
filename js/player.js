(function($) {
    'use strict';

    class LautFMPlayer {
        constructor() {
            this.audio = new Audio(lautfmData.stream_url);
            this.isPlaying = false;
            this.currentVolume = 0.8;
            this.initializePlayer();
            this.startMetadataUpdate();
        }

        initializePlayer() {
            const self = this;
            
            $('.play-pause').on('click', function() {
                if (self.isPlaying) {
                    self.pause();
                } else {
                    self.play();
                }
            });

            $('.volume-slider').on('input', function() {
                const value = $(this).val() / 100;
                self.setVolume(value);
                $('.volume-slider').val($(this).val());
            });

            this.audio.volume = this.currentVolume;
        }

        play() {
            this.audio.play();
            this.isPlaying = true;
            $('.play-pause').text('Pause');
        }

        pause() {
            this.audio.pause();
            this.isPlaying = false;
            $('.play-pause').text('Play');
        }

        setVolume(value) {
            this.currentVolume = value;
            this.audio.volume = value;
        }

        startMetadataUpdate() {
            this.fetchMetadata();
            setInterval(() => {
                this.fetchMetadata();
            }, 5000);
        }

        fetchMetadata() {
            $.ajax({
                url: `https://api.laut.fm/station/${lautfmData.station}/current_song`,
                method: 'GET',
                success: function(response) {
                    if (response) {
                        // Korrigierte Behandlung der Künstler- und Titelinformationen
                        let artistName = 'Unknown Artist';
                        let songTitle = 'Unknown Song';

                        if (response.artist && typeof response.artist === 'string') {
                            artistName = response.artist;
                        } else if (response.artist && response.artist.name) {
                            artistName = response.artist.name;
                        }

                        if (response.title) {
                            songTitle = response.title;
                        }

                        $('.artist').text(artistName);
                        $('.song').text(songTitle);
                    }
                },
                error: function() {
                    $('.artist').text('Unknown Artist');
                    $('.song').text('Unknown Song');
                }
            });
        }
    }

    $(document).ready(function() {
        new LautFMPlayer();
    });

})(jQuery);