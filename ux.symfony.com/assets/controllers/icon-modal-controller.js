import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);
    }

    connect() {
        window.addEventListener('Icon:Clicked', this.onIconClick.bind(this));
        this.element.addEventListener('click', this.onClick.bind(this));
        // if (this.element.dataset.open) {
        //     this.show();
        // }
    }

    disconnect() {
        this.element.removeEventListener('click', this.onClick.bind(this));
        window.removeEventListener('Icon:Clicked', this.onIconClick.bind(this));
    }

    show() {
        this.element.showModal();
        this.element.dataset.open = true;
    }

    close() {
        //this.element.close();
        this.element.dataset.open = false;
    }

    onIconClick(event) {
        // this.show();
        const input = this.element.querySelector('input');
        input.value = event.detail.icon;
        input.dispatchEvent(new Event('change', {bubbles: true}));
    }

    onClick(event) {
        const dialogDimensions = this.element.getBoundingClientRect()
        if (
            event.clientX < dialogDimensions.left ||
            event.clientX > dialogDimensions.right ||
            event.clientY < dialogDimensions.top ||
            event.clientY > dialogDimensions.bottom
        ) {
            this.close()
        }
    }
}
