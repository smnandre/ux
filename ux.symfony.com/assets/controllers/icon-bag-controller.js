import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static targets = [ "count" ]

    initialize() {
        // const icons = (localStorage.getItem('icon-set') ?? '').split(',');
        // this.iconSet = new Set(icons);
    }

    connect() {
        if (this.countTarget) {
            this.countTarget.textContent = Math.round(Math.random() * 10);
        }
    }

    disconnect() {
    }
}
