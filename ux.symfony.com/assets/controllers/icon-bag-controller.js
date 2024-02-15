import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = [ "count" ]

    initialize() {
        // const icons = (localStorage.getItem('icon-set') ?? '').split(',');
        // this.iconSet = new Set(icons);
        console.log('sdf');
    }

    connect() {
        console.log('connect');
        if (this.countTarget) {
            this.countTarget.textContent = Math.round(Math.random() * 10);
        }
    }

    disconnect() {
        console.log('disconnect');
    }
}
