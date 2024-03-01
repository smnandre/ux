import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {



    connect() {

    }

    disconnect() {

    }

    reduce() {
        this.element.classList.toggle('Browser--reduced');
    }

}
