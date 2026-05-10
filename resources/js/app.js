
const createBookShelfCarousel = () => ({
		hasOverflow: false,
		canScrollPrev: false,
		canScrollNext: false,

		setup() {
			this.$nextTick(() => {
				this.updateControls();
			});
		},

		getTrack() {
			return this.$refs.track ?? null;
		},

		getTrackGap() {
			const track = this.getTrack();

			if (!track) {
				return 0;
			}

			const styles = window.getComputedStyle(track);

			return Number.parseFloat(styles.columnGap || styles.gap || '0');
		},

		getSlideSpan() {
			const track = this.getTrack();
			const slide = track?.querySelector('[data-book-carousel-slide]');

			if (!track || !slide) {
				return track?.clientWidth ?? 0;
			}

			return slide.getBoundingClientRect().width + this.getTrackGap();
		},

		getScrollAmount() {
			const track = this.getTrack();
			const slideSpan = this.getSlideSpan();

			if (!track || slideSpan <= 0) {
				return 0;
			}

			const visibleSlides = Math.max(1, Math.floor((track.clientWidth + this.getTrackGap()) / slideSpan));

			return Math.max(slideSpan, slideSpan * Math.max(1, visibleSlides - 1));
		},

		scroll(direction) {
			const track = this.getTrack();

			if (!track) {
				return;
			}

			const maxScrollLeft = Math.max(0, track.scrollWidth - track.clientWidth);
			const nextScrollLeft = Math.max(
				0,
				Math.min(maxScrollLeft, track.scrollLeft + (direction * this.getScrollAmount())),
			);

			track.scrollTo({
				left: nextScrollLeft,
				behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
			});

			this.updateControls();
		},

		prev() {
			this.scroll(-1);
		},

		next() {
			this.scroll(1);
		},

		updateControls() {
			const track = this.getTrack();

			if (!track) {
				return;
			}

			const maxScrollLeft = Math.max(0, track.scrollWidth - track.clientWidth);
			const scrollThreshold = 8;

			this.hasOverflow = maxScrollLeft > scrollThreshold;
			this.canScrollPrev = track.scrollLeft > scrollThreshold;
			this.canScrollNext = track.scrollLeft < (maxScrollLeft - scrollThreshold);
		},
	});

window.bookShelfCarousel = createBookShelfCarousel;

document.addEventListener('alpine:init', () => {
	window.Alpine.data('bookShelfCarousel', createBookShelfCarousel);
});
