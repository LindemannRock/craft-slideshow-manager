/**
 * Slideshow Manager Field
 *
 * Control panel field interface for managing slideshows
 */

(function() {
    'use strict';

    // Create namespace
    if (typeof window.SlideshowManager === 'undefined') {
        window.SlideshowManager = {};
    }

    /**
     * Slideshow Field Class
     */
    class SlideshowField {
        constructor(id, config) {
            this.id = id;
            this.config = config;
            this.$container = document.getElementById(id);
            this.$input = this.$container.querySelector('.slideshow-data-input');
            this.$slidesContainer = this.$container.querySelector('.slideshow-slides-container');

            this.init();
        }

        init() {
            // Bind event listeners
            this.bindEvents();

            // Initialize sortable for drag-and-drop
            this.initSortable();

            // Load existing slides
            this.loadSlides();
        }

        bindEvents() {
            // Add slide buttons
            const addButtons = this.$container.querySelectorAll('.slideshow-add-slide');
            addButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const slideType = e.currentTarget.dataset.slideType;
                    this.addSlide(slideType);
                });
            });

            // Delegate events for slide actions
            this.$slidesContainer.addEventListener('click', (e) => {
                const toggleBtn = e.target.closest('.slideshow-slide-toggle');
                const deleteBtn = e.target.closest('.slideshow-slide-delete');

                if (toggleBtn) {
                    this.toggleSlide(toggleBtn);
                } else if (deleteBtn) {
                    this.deleteSlide(deleteBtn);
                }
            });
        }

        initSortable() {
            // Placeholder for Sortable.js integration
            // Will be implemented with proper drag-and-drop library
            console.log('Sortable initialized for:', this.id);
        }

        loadSlides() {
            const data = this.getData();
            if (data && data.slides && data.slides.length > 0) {
                // Slides already rendered in template
                console.log('Loaded', data.slides.length, 'slides');
            }
        }

        addSlide(type) {
            const data = this.getData() || { slides: [], config: {} };
            const order = data.slides.length;

            const newSlide = {
                type: type,
                content: null,
                settings: {},
                order: order,
                cssClass: null,
                metadata: {}
            };

            data.slides.push(newSlide);
            this.setData(data);

            // Re-render slides
            this.renderSlides();
        }

        deleteSlide(button) {
            if (!confirm('Are you sure you want to delete this slide?')) {
                return;
            }

            const slideItem = button.closest('.slideshow-slide-item');
            const order = parseInt(slideItem.dataset.order);
            const data = this.getData();

            if (data && data.slides) {
                data.slides.splice(order, 1);
                // Reorder remaining slides
                data.slides.forEach((slide, index) => {
                    slide.order = index;
                });
                this.setData(data);
                this.renderSlides();
            }
        }

        toggleSlide(button) {
            const slideItem = button.closest('.slideshow-slide-item');
            const content = slideItem.querySelector('.slideshow-slide-content');
            slideItem.classList.toggle('collapsed');
        }

        renderSlides() {
            // Placeholder - will render slides dynamically
            console.log('Rendering slides...');
        }

        getData() {
            try {
                return JSON.parse(this.$input.value || '{}');
            } catch (e) {
                return null;
            }
        }

        setData(data) {
            this.$input.value = JSON.stringify(data);
        }
    }

    // Export to namespace
    window.SlideshowManager.SlideshowField = SlideshowField;

})();
