import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {

    async initialize() {
        this.component = await getComponent(this.element);

        this.component.on('render:finished', (component) => {
            // do something after the component re-renders
            console.log('render:finished');

            this.element.showModal();

        });
    }

    close() {
        this.element.close();
    }

    connect() {

        // e.g. set some live property called "mode" on your component
        // this.component.set('mode', 'editing');
        // then, trigger a re-render to get the fresh HTML
        //this.component.render();

        // this.element.showModal();

        // this.component.on('render:finished', (component) => {
        //     // do something after the component re-renders
        //     console.log('render:finished');
        // });

    }
}
