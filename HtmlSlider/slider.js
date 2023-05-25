class Slider {

    /**
     * 
     * @param {Array<Element>} thumbs Tableau contenant les points du slider, gauche et droite
     * @param {Element} range 
     * @param {Array} values 
     * @param {Array<Element>} thumbValues 
     * @param {Element} slider Par défaut l'element parent de "range"
     */
    constructor(thumbs, range, values, thumbValues, slider = null) {
        try {
            this.leftThumb = thumbs[0];
            this.rightThumb = thumbs[1];

            this.leftValue = thumbValues[0];
            this.rightValue = thumbValues[1];

            this.range = range;
            this.slider = slider == null ? range.parentElement : slider;
            this.values = values;
        } catch (error) {
            console.log("Initialisation échouée", error);
        }

        this.moveLeftThumbEvent = this.moveLeftThumb.bind(this);
        this.moveRightThumbEvent = this.moveRightThumb.bind(this);
        this.stopDraggingEvent = this.stopDragging.bind(this);
    }

    static getMousePosition(event) {
        return event.clientX || event.touches[0].clientX;
    }

    moveLeftThumb(event) {
        let mousePosition = PhenixSlider.getMousePosition(event);
        let newPosition = mousePosition - this.slider.getBoundingClientRect().left - this.leftThumb.offsetWidth / 2;
        let rightThumbPosition = this.rightThumb.offsetLeft - this.rightThumb.offsetWidth / 2;

        if (newPosition < 0) {
            newPosition = 0;
        } else if (newPosition > rightThumbPosition) {
            newPosition = rightThumbPosition;
        }
        let newValueIndex = Math.round(newPosition / (this.slider.offsetWidth - this.leftThumb.offsetWidth) * (this.values.length - 1));
        newPosition = newValueIndex * (this.slider.offsetWidth - this.leftThumb.offsetWidth) / (this.values.length - 1);

        this.leftThumb.style.left = newPosition + 'px';
        this.range.style.left = newPosition + 'px';
        this.range.style.width = this.rightThumb.offsetLeft - newPosition + 'px';

        this.leftValue.textContent = this.values[newValueIndex];
        this.leftValue.style.left = newPosition + 'px';
    }

    moveRightThumb(event) {
        let mousePosition = PhenixSlider.getMousePosition(event);
        let newPosition = mousePosition - this.slider.getBoundingClientRect().left - this.rightThumb.offsetWidth / 2;
        let leftThumbPosition = this.leftThumb.offsetLeft + this.leftThumb.offsetWidth / 2;

        if (newPosition > this.slider.offsetWidth - this.rightThumb.offsetWidth) {
            newPosition = this.slider.offsetWidth - this.rightThumb.offsetWidth;
        } else if (newPosition < leftThumbPosition) {
            newPosition = leftThumbPosition;
        }
        let newValueIndex = Math.round(newPosition / (this.slider.offsetWidth - this.rightThumb.offsetWidth) * (this.values.length - 1));
        newPosition = newValueIndex * (this.slider.offsetWidth - this.rightThumb.offsetWidth) / (this.values.length - 1);

        this.rightThumb.style.left = newPosition + 'px';
        this.range.style.width = newPosition - this.leftThumb.offsetLeft + 'px';

        this.rightValue.textContent = this.values[newValueIndex];
        this.rightValue.style.left = newPosition + 'px';
    }

    stopDragging() {
        document.removeEventListener('mousemove', this.moveLeftThumbEvent);
        document.removeEventListener('mousemove', this.moveRightThumbEvent);
        document.removeEventListener('touchmove', this.moveLeftThumbEvent);
        document.removeEventListener('touchmove', this.moveRightThumbEvent);

        document.removeEventListener('mouseup', this.stopDraggingEvent);
        document.removeEventListener('touchend', this.stopDraggingEvent);
    }

    startDraggingLeft(event) {
        event.preventDefault();

        document.addEventListener('mousemove', this.moveLeftThumbEvent);
        document.addEventListener('touchmove', this.moveLeftThumbEvent);

        document.addEventListener('mouseup', this.stopDraggingEvent);
        document.addEventListener('touchend', this.stopDraggingEvent);

        this.moveLeftThumb(event);
    }

    startDraggingRight(event) {
        event.preventDefault();

        document.addEventListener('mousemove', this.moveRightThumbEvent);
        document.addEventListener('touchmove', this.moveRightThumbEvent);

        document.addEventListener('mouseup', this.stopDraggingEvent);
        document.addEventListener('touchend', this.stopDraggingEvent);

        this.rightThumb.classList.add('active');

        this.moveRightThumb(event);
    }

    activate() {
        this.leftThumb.addEventListener('mousedown', (event) => this.startDraggingLeft(event));
        this.leftThumb.addEventListener('touchstart', (event) => this.startDraggingLeft(event));

        this.rightThumb.addEventListener('mousedown', (event) => this.startDraggingRight(event));
        this.rightThumb.addEventListener('touchstart', (event) => this.startDraggingRight(event));
    }
}