import {Controller} from '@hotwired/stimulus';
import {delegate} from 'tippy.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect() {
        this.element.addEventListener('click', this.click.bind(this), true);
        this.tippy = delegate(this.element, {
            target: 'body[data-icon-size="small"] .IconCard',
            content: (reference) => reference.title,
            arrow: true,
            theme: 'translucent',
            delay: [200, 0],
        });
    }

    disconnect() {
        this.element.removeEventListener('click', this.click.bind(this), true);
        this.tippy.destroy();
    }

    click(event) {
        const iconCard = event.target.closest('.IconCard');
        if (!iconCard) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();

        const customEvent = new CustomEvent('Icon:Clicked', { detail: { icon: iconCard.title }, bubbles: true });
        window.dispatchEvent(customEvent);
    }

}
