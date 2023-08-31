import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        target: String,
    }

    connect() {
        this.element.addEventListener('click', (event) => {
            event.preventDefault();

            this.openModal();
        });
    }

    openModal() {
        const targetId = this.targetValue;
        const target = document.getElementById(targetId);
        if (!target) {
            throw new Error(`Could not find target element with id "${targetId}"`);
        }

        target.dispatchEvent(new CustomEvent('modal:open'));
    }
}
