import {PluginInterface} from "./PluginInterface";
import Component from "../index";

export default class implements PluginInterface {
    intersectionObserver : IntersectionObserver;

    constructor() {
        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.dispatchEvent(new CustomEvent('live:intersect', {detail: entry}));
                    entry.target.classList.add('loading');
                }
            });
        });
    }

    attachToComponent(component: Component): void {
        component.on('connect', () => {
            this.intersectionObserver.observe(component.element);
        });
        component.on('disconnect', () => {
            this.intersectionObserver.unobserve(component.element);
        });
    }
}

//
//
// const intersectionObserver = new IntersectionObserver((entries) => {
//   // If intersectionRatio is 0, the target is out of view
//   // and we do not need to do anything.
//   if (entries[0].intersectionRatio <= 0) return;
//
//   loadItems(10);
//   console.log("Loaded new items");
// });
// // start observing
// intersectionObserver.observe(document.querySelector(".scrollerFooter"));
