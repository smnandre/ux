import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    dispatch(event) {
        event.preventDefault();

        const targetSelector = event.params.target;
        const target = document.querySelector(targetSelector);
        if (!target) {
            throw new Error(`Could not find target element with selector "${targetSelector}"`);
        }

        target.dispatchEvent(new CustomEvent(event.params.event));
    }
}
