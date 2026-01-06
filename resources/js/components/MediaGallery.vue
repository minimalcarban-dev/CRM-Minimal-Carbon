<template>
    <div class="media-gallery">
        <div class="media-grid">
            <button
                v-for="(img, idx) in visibleImages"
                :key="img.id || idx"
                class="media-item"
                @click="openAt(idx)"
                :aria-label="`Open image ${idx + 1}`"
            >
                <img
                    :src="thumbUrl(img)"
                    :alt="img.filename || 'image'"
                    loading="lazy"
                />
                <div
                    v-if="idx === maxVisible - 1 && remainingCount > 0"
                    class="overlay"
                    role="button"
                    tabindex="0"
                    @click.stop="openAt(idx)"
                    @keydown.enter.prevent="openAt(idx)"
                    :aria-label="`Open gallery, ${remainingCount} more images`"
                >
                    <span class="overlay-text">+{{ remainingCount }}</span>
                </div>
            </button>
        </div>

        <!-- Lightbox -->
        <div
            v-if="lightboxOpen"
            class="lightbox"
            @keydown.esc="close"
            tabindex="-1"
        >
            <button class="lb-close" @click="close" aria-label="Close gallery">
                ✕
            </button>
            <button class="lb-prev" @click="prev" aria-label="Previous image">
                ‹
            </button>
            <img
                class="lb-image"
                :src="fullUrl(currentImage)"
                :alt="currentImage?.filename || 'image'"
            />
            <button class="lb-next" @click="next" aria-label="Next image">
                ›
            </button>
            <div class="lb-counter">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "MediaGallery",
    props: {
        images: { type: Array, default: () => [] },
        maxVisible: { type: Number, default: 6 },
    },
    data() {
        return {
            lightboxOpen: false,
            currentIndex: 0,
        };
    },
    computed: {
        visibleImages() {
            return this.images.slice(0, this.maxVisible);
        },
        remainingCount() {
            return Math.max(0, this.images.length - this.maxVisible);
        },
        currentImage() {
            return this.images[this.currentIndex] || null;
        },
    },
    methods: {
        thumbUrl(img) {
            if (!img) return "";
            if (img.thumbnail_url) return img.thumbnail_url;
            if (img.thumbnail_path && img.thumbnail_path.startsWith("http"))
                return img.thumbnail_path;
            if (img.thumbnail_path) return `/storage/${img.thumbnail_path}`;
            if (img.url) return img.url;
            if (img.path && img.path.startsWith("http")) return img.path;
            if (img.path) return `/storage/${img.path}`;
            return "";
        },
        fullUrl(img) {
            if (!img) return "";
            if (img.url) return img.url;
            if (img.path && img.path.startsWith("http")) return img.path;
            if (img.path) return `/storage/${img.path}`;
            if (img.thumbnail_url) return img.thumbnail_url;
            if (img.thumbnail_path && img.thumbnail_path.startsWith("http"))
                return img.thumbnail_path;
            if (img.thumbnail_path) return `/storage/${img.thumbnail_path}`;
            return "";
        },
        openAt(idx) {
            // If clicked on last visible tile and there are more, open at that index
            const realIdx = idx;
            this.currentIndex = realIdx;
            this.lightboxOpen = true;
            this.$nextTick(() => {
                const lb = this.$el.querySelector(".lightbox");
                if (lb) lb.focus();
            });
            window.addEventListener("keydown", this.onKey);
        },
        close() {
            this.lightboxOpen = false;
            window.removeEventListener("keydown", this.onKey);
        },
        prev() {
            this.currentIndex =
                (this.currentIndex - 1 + this.images.length) %
                this.images.length;
        },
        next() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        onKey(e) {
            if (!this.lightboxOpen) return;
            if (e.key === "ArrowLeft") this.prev();
            if (e.key === "ArrowRight") this.next();
            if (e.key === "Escape") this.close();
        },
    },
    beforeUnmount() {
        window.removeEventListener("keydown", this.onKey);
    },
};
</script>

<style scoped>
.media-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.media-item {
    position: relative;
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
}
.media-item img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: 8px;
    display: block;
}
.overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.56);
    color: #fff;
    border-radius: 8px;
}
.overlay-text {
    font-size: 20px;
    font-weight: 700;
}
.lightbox {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.85);
    z-index: 100000;
}
.lb-image {
    max-width: 80%;
    max-height: 80%;
    border-radius: 6px;
}
.lb-close,
.lb-prev,
.lb-next {
    position: absolute;
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
}
.lb-close {
    top: 20px;
    right: 20px;
}
.lb-prev {
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 24px;
}
.lb-next {
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 24px;
}
.lb-counter {
    position: absolute;
    bottom: 20px;
    color: #fff;
    font-weight: 600;
}
</style>
