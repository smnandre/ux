import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['map', 'markers'];

    connect() {
        console.log('Map controller connected');
    }

    mapTargetConnected(element) {
        console.log('mapTargetConnected', element);
        this.mapTarget.addEventListener('map:loaded', this.onMapLoaded.bind(this));
    }

    onMapLoaded(event) {
        console.log('onMapLoaded', event);
        this.map = event.detail.map;
        this.markersTargets.forEach(marker => {
            this.map.addMarker(marker);
        });
    }
}
