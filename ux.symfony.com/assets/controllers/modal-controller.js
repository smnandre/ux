import { Controller } from '@hotwired/stimulus';
import { useClickOutside } from 'stimulus-use'

export default class extends Controller {
    static values = {
        displayClass: {
            type: String,
            default: 'flex'
        },
        startOpen: {
            type: Boolean,
            default: false
        }
    }

    static targets = ['wrapper'];

    overlayElement = null;

    connect() {
        useClickOutside(this, {
            element: this.wrapperTarget
        });

        if (this.startOpenValue) {
            this.#addOverlay();
            this.element.focus();
        }
    }

    /**
     * @param {MouseEvent} event
     */
    close(event) {
        event.preventDefault();

        this.#doClose();
    }

    open(event) {
        event.preventDefault();

        this.#doOpen();
    }

    clickOutside(event) {
        this.#doClose();
    }

    #doOpen() {
        this.element.classList.remove('hidden');
        this.element.classList.add(this.displayClassValue);
        this.#addOverlay();
        this.element.focus();
    }

    #doClose() {
        this.element.classList.add('hidden');
        this.element.classList.remove(this.displayClassValue);
        this.#removeOverlay();
    }

    #addOverlay() {
        const overlayElement = document.createElement('div');
        overlayElement.setAttribute('modal-backdrop', '');
        overlayElement.className = 'bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40';
        document.body.appendChild(overlayElement);
        this.overlayElement = overlayElement;
    }

    #removeOverlay() {
        if (this.overlayElement) {
            this.overlayElement.remove();
            this.overlayElement = null;
        }
    }

    /*
     * Using a "fake" target because a real target would result in data-modal-target="", which Flowbite's
     * JavaScript thinks is meant for it. We could change to a real target once Flowbite JavaScript is removed.
     */
    get contentTarget() {
        return this.element.querySelector('[data-modal-manual-target="content"]');
    }
}
