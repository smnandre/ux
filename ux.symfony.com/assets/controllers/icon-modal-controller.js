import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);
        this.element.addEventListener('render', () => {this.element.showModal()});
    }

    connect() {
        window.addEventListener('Icon:Clicked', this.onIconClicked.bind(this));

        this.element.addEventListener('click', this.onClick.bind(this));
    }

    onIconClicked(event) {
        console.log(event.detail);
        console.log(event.detail.icon);

        const input = this.element.querySelector('input');
        input.value = event.detail.icon;
        input.dispatchEvent(new Event('change', {bubbles: true}));
        this.element.showModal();
    }

    onClick(event) {
        const dialogDimensions = this.element.getBoundingClientRect()
        if (
            event.clientX < dialogDimensions.left ||
            event.clientX > dialogDimensions.right ||
            event.clientY < dialogDimensions.top ||
            event.clientY > dialogDimensions.bottom
        ) {
            this.element.close()
        }
    }
}
