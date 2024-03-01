import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader'];

    connect() {
        console.log('connect');
    }

    loaderTargetConnected(element) {
        console.log('loaderTargetConnect');
        this.observer ??= new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.dispatchEvent(new CustomEvent('appear', {detail: {entry}}));
                }
            });
        });

        console.log('observe');
        this.observer?.observe(element);
    }

    loaderTargetDisconnected(element) {
        console.log('loaderTargetDisconnect');
        this.observer?.unobserve(element);
    }

}
